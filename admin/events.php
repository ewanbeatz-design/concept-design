<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (!admin_user()) {
    http_response_code(401);
    exit;
}

// ---------- Отключаем буферизацию и ставим правильные заголовки ----------
@ini_set('zlib.output_compression', '0');
@ini_set('output_buffering', '0');
@ini_set('implicit_flush', '1');

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('X-Accel-Buffering: no');       // для Nginx
header('Connection: keep-alive');

// ---------- Последний ID, известный клиенту ----------
$lastId       = (int)($_GET['last_id'] ?? 0);
$lastCheckTs  = time();

// Отправляем стартовое состояние
function sse_send(string $event, array $data): void {
    echo "event: {$event}\n";
    echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    @flush();
}

// Отправляем heartbeat каждые 15 секунд, чтобы прокси не рвали соединение
$startTime = time();
$maxLifetime = 60 * 30; // 30 минут, потом клиент переподключится

// Текущее максимальное ID
$maxId = (int)$pdo->query("SELECT COALESCE(MAX(id), 0) FROM leads")->fetchColumn();

// Первичный ответ — сколько сейчас новых всего
$newCount = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE status='new'")->fetchColumn();

sse_send('hello', [
    'max_id'    => $maxId,
    'new_count' => $newCount,
    'server_ts' => time(),
]);

// Снимок статусов (для отслеживания изменений другими менеджерами)
$statusesSnapshot = [];
$stmt = $pdo->query("SELECT id, status FROM leads");
foreach ($stmt->fetchAll() as $row) {
    $statusesSnapshot[(int)$row['id']] = $row['status'];
}

// ---------- Основной цикл ----------
while (true) {
    // Проверяем, не отвалился ли клиент
    if (connection_aborted()) {
        break;
    }

    // Ограничение по времени жизни соединения
    if ((time() - $startTime) > $maxLifetime) {
        sse_send('reconnect', ['reason' => 'lifetime']);
        break;
    }

    // 1) Новые заявки с ID больше last_id
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM leads
            WHERE id > ?
            ORDER BY id ASC
            LIMIT 50
        ");
        $stmt->execute([$lastId]);
        $newLeads = $stmt->fetchAll();

        foreach ($newLeads as $lead) {
            sse_send('lead.created', [
                'lead' => [
                    'id'         => (int)$lead['id'],
                    'name'       => $lead['name'],
                    'phone'      => $lead['phone'],
                    'email'      => $lead['email'],
                    'source'     => $lead['source'],
                    'status'     => $lead['status'],
                    'comment'    => $lead['comment'],
                    'created_at' => $lead['created_at'],
                    'created_h'  => (new DateTime($lead['created_at']))->format('d.m H:i'),
                ],
            ]);
            $lastId = (int)$lead['id'];
            $statusesSnapshot[$lastId] = $lead['status'];
        }
    } catch (Throwable $e) {
        // тихо игнорим
    }

    // 2) Изменившиеся статусы
    try {
        $stmt = $pdo->query("SELECT id, status FROM leads");
        $currentStatuses = [];
        foreach ($stmt->fetchAll() as $row) {
            $currentStatuses[(int)$row['id']] = $row['status'];
        }

        foreach ($currentStatuses as $id => $status) {
            if (($statusesSnapshot[$id] ?? null) !== $status) {
                sse_send('lead.status_changed', [
                    'lead_id' => $id,
                    'status'  => $status,
                ]);
                $statusesSnapshot[$id] = $status;
            }
        }

        // Удалённые
        foreach ($statusesSnapshot as $id => $status) {
            if (!isset($currentStatuses[$id])) {
                sse_send('lead.deleted', ['lead_id' => $id]);
                unset($statusesSnapshot[$id]);
            }
        }
    } catch (Throwable $e) {}

    // 3) Обновлённый счётчик новых заявок
    try {
        $newCount = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE status='new'")->fetchColumn();
        sse_send('counters', ['new_count' => $newCount]);
    } catch (Throwable $e) {}

    // 4) Heartbeat
    echo ": ping " . time() . "\n\n";
    @flush();

    sleep(3);
}