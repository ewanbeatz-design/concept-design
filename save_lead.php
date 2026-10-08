<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/json; charset=utf-8');

/* ============================================================
   НАСТРОЙКИ TELEGRAM
   ============================================================ */
$tgBotToken = getenv('TG_BOT_TOKEN') ?: '';
$tgChatId   = getenv('TG_CHAT_ID') ?: '';

/* ============================================================
   ОТПРАВКА В TELEGRAM
   ============================================================ */
function tg_send(string $text): void {
    global $tgBotToken, $tgChatId;
    if ($tgBotToken === '' || $tgChatId === '') return;

    $url = 'https://api.telegram.org/bot' . $tgBotToken . '/sendMessage';

    $payload = http_build_query([
        'chat_id'    => TG_CHAT_ID,
        'text'       => $text,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true,
    ]);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST               => true,
            CURLOPT_POSTFIELDS         => $payload,
            CURLOPT_RETURNTRANSFER     => true,
            CURLOPT_TIMEOUT            => 2,
            CURLOPT_CONNECTTIMEOUT     => 1,
            CURLOPT_SSL_VERIFYPEER     => false,
            CURLOPT_SSL_VERIFYHOST     => 0,
            CURLOPT_NOSIGNAL           => true,
        ]);
        @curl_exec($ch);
        @curl_close($ch);
        return;
    }

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 2,
        ],
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ],
    ]);
    @file_get_contents($url, false, $ctx);
}

/* ============================================================
   ХЕЛПЕРЫ
   ============================================================ */
function esc(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function json_out(array $data): void {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/* Человеческие названия полей */
function field_label(string $key): string {
    $map = [
        // Кухня
        'kitchen_form'       => 'Форма кухни',
        'kitchen_height'     => 'Высота (мм)',
        'kitchen_width'      => 'Ширина (мм)',
        'kitchen_depth'      => 'Глубина (мм)',
        'kitchen_furniture'  => 'Класс фурнитуры',
        'kitchen_facade'     => 'Материал фасадов',
        'kitchen_countertop' => 'Материал столешницы',
        'kitchen_appliances' => 'Тип техники',
        'kitchen_budget'     => 'Бюджет',
        'kitchen_light'      => 'Подсветка',
        'kitchen_style'      => 'Стиль',
        'kitchen_timing'     => 'Срочность',

        // Шкаф-купе
        'wardrobe_type'      => 'Тип шкафа',
        'wardrobe_height'    => 'Высота (мм)',
        'wardrobe_width'     => 'Ширина (мм)',
        'wardrobe_depth'     => 'Глубина (мм)',
        'wardrobe_material'  => 'Материал корпуса',
        'wardrobe_filling'   => 'Наполнение',
        'wardrobe_system'    => 'Система открывания',
        'wardrobe_budget'    => 'Бюджет',

        // Гардеробная
        'dressing_type'      => 'Тип гардеробной',
        'dressing_area'      => 'Площадь (м²)',
        'dressing_material'  => 'Материал отделки',
        'dressing_filling'   => 'Наполнение',
        'dressing_light'     => 'Подсветка',
        'dressing_budget'    => 'Бюджет',

        // Прихожая
        'hallway_items'      => 'Состав прихожей',
        'hallway_size'       => 'Размер прихожей',
        'hallway_material'   => 'Материал',
        'hallway_budget'     => 'Бюджет',

        // Детская
        'kids_age'           => 'Возраст ребёнка',
        'kids_items'         => 'Что нужно',
        'kids_material'      => 'Материал',
        'kids_color'         => 'Цвет',
        'kids_budget'        => 'Бюджет',

        // Комод
        'chest_type'         => 'Тип',
        'chest_drawers'      => 'Количество ящиков',
        'chest_material'     => 'Материал',
        'chest_budget'       => 'Бюджет',

        // Квиз — мебель
        'furniture_type'     => 'Что нужно',
        'furniture_size'     => 'Размер',
        'furniture_material' => 'Материал фасадов',
        'furniture_style'    => 'Стиль',
        'furniture_budget'   => 'Бюджет',
        'furniture_timing'   => 'Сроки',

        // Квиз — интерьер
        'interior_object'    => 'Объект',
        'interior_area'      => 'Площадь',
        'interior_format'    => 'Формат проекта',
        'interior_style'     => 'Стиль',
        'interior_budget'    => 'Бюджет',
        'interior_timing'    => 'Сроки',

        // Общие
        'quiz_type'          => 'Направление',
        'type'               => 'Что проектируем',
        'size'               => 'Площадь / длина',
        'style'              => 'Стиль',
        'contact'            => 'Контакт',
        'calc'               => 'Расчёт',
    ];
    return $map[$key] ?? ucfirst(str_replace('_', ' ', $key));
}

/* ============================================================
   ОБРАБОТКА ЗАЯВКИ
   ============================================================ */
try {
    // ---------- Принимаем данные ----------
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        $data = $_POST;
    }

    // ---------- Валидация ----------
    $name    = trim((string)($data['name']    ?? ''));
    $phone   = trim((string)($data['phone']   ?? ''));
    $email   = trim((string)($data['email']   ?? ''));
    $message = trim((string)($data['message'] ?? $data['comment'] ?? ''));
    $source  = trim((string)($data['source']  ?? 'Форма на сайте'));

    if ($name === '' || $phone === '') {
        json_out(['success' => false, 'message' => 'Укажите имя и телефон']);
    }

    // Honeypot
    if (!empty($data['website'])) {
        json_out(['success' => true, 'message' => 'Заявка принята']);
    }

    // ---------- Доп. поля ----------
    $extraKeys = [
        'project_id', 'project_title', 'page_url', 'form_type',
        'type', 'size', 'style', 'contact', 'calc',
        'quiz_type',

        // Кухня
        'kitchen_form', 'kitchen_furniture', 'kitchen_facade',
        'kitchen_countertop', 'kitchen_appliances', 'kitchen_light',
        'kitchen_style', 'kitchen_timing', 'kitchen_budget',
        'kitchen_width', 'kitchen_depth', 'kitchen_height',

        // Шкаф
        'wardrobe_type', 'wardrobe_material', 'wardrobe_system',
        'wardrobe_filling', 'wardrobe_budget',
        'wardrobe_width', 'wardrobe_depth', 'wardrobe_height',

        // Гардеробная
        'dressing_type', 'dressing_material', 'dressing_filling',
        'dressing_light', 'dressing_budget', 'dressing_area',

        // Прихожая
        'hallway_items', 'hallway_size', 'hallway_material', 'hallway_budget',

        // Детская
        'kids_age', 'kids_items', 'kids_material', 'kids_color', 'kids_budget',

        // Комод
        'chest_type', 'chest_drawers', 'chest_material', 'chest_budget',

        // Квиз — мебель
        'furniture_type', 'furniture_size', 'furniture_material',
        'furniture_style', 'furniture_budget', 'furniture_timing',

        // Квиз — интерьер
        'interior_object', 'interior_area', 'interior_format',
        'interior_style', 'interior_budget', 'interior_timing',

        'project_info',
    ];

    $extra = [];
    foreach ($extraKeys as $k) {
        if (!empty($data[$k])) {
            if (is_array($data[$k])) {
                $extra[$k] = implode(', ', array_map('strval', $data[$k]));
            } else {
                $extra[$k] = trim((string)$data[$k]);
            }
        }
    }

    $projectInfo = null;
    if (!empty($data['project_info'])) {
        $projectInfo = is_string($data['project_info'])
            ? $data['project_info']
            : json_encode($data['project_info'], JSON_UNESCAPED_UNICODE);
    }

    if (!$pdo) {
        json_out(['success' => false, 'message' => 'Ошибка сервера']);
    }

    // ---------- Сохраняем в БД ----------
    $stmt = $pdo->prepare("
        INSERT INTO leads (name, phone, email, source, status, quiz_data, project_info, comment, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'new', ?, ?, ?, NOW(), NOW())
    ");

    $stmt->execute([
        $name,
        $phone,
        $email ?: null,
        $source,
        $extra ? json_encode($extra, JSON_UNESCAPED_UNICODE) : null,
        $projectInfo,
        $message ?: null,
    ]);

    $leadId = (int)$pdo->lastInsertId();

    // ---------- СНАЧАЛА отвечаем пользователю ----------
    echo json_encode([
        'success' => true,
        'message' => 'Заявка принята',
        'lead_id' => $leadId,
    ], JSON_UNESCAPED_UNICODE);

    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } else {
        if (ob_get_level() > 0) @ob_end_flush();
        @flush();
    }

    // ---------- ПОТОМ отправляем в Telegram ----------
    $tg  = "<b>🔔 Новая заявка #{$leadId}</b>\n\n";
    $tg .= "<b>👤 Имя:</b> "     . esc($name)   . "\n";
    $tg .= "<b>📞 Телефон:</b> " . esc($phone)  . "\n";

    if ($email)   $tg .= "<b>✉️ Email:</b> "    . esc($email)   . "\n";
    if ($source)  $tg .= "<b>📌 Источник:</b> " . esc($source)  . "\n";
    if ($message) $tg .= "<b>💬 Сообщение:</b>\n" . esc($message) . "\n";

    if (!empty($extra)) {
        $tg .= "\n<b>📋 Детали:</b>\n";
        foreach ($extra as $k => $v) {
            if ($v === '' || $v === null) continue;
            if ($k === 'project_info') continue;

            $label = field_label($k);
            $tg .= "• <b>" . esc($label) . ":</b> " . esc((string)$v) . "\n";
        }
    }

    if ($projectInfo) {
        $decoded = json_decode($projectInfo, true);
        if (is_array($decoded) && !empty($decoded['title'])) {
            $tg .= "\n<b>📁 Проект:</b> " . esc($decoded['title']) . "\n";
        }
    }

    $tg .= "\n<b>🕒 Время:</b> " . date('d.m.Y H:i:s');

    tg_send($tg);

    exit;

} catch (Throwable $e) {
    error_log('Lead error: ' . $e->getMessage());
    json_out(['success' => false, 'message' => 'Ошибка сервера']);
}