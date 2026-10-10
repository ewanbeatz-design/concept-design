<?php
/**
 * CONCEPT DESIGN — общий конфиг
 * Подключение к БД + утилиты
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------- Подключение к БД ----------
// Production: секретный конфиг хранится в includes/concept-design-config.php.
// Значения из него нельзя перезаписывать пустыми значениями по умолчанию.
$pdo = null;

unset($dbHost, $dbName, $dbUser, $dbPass);
$serverConfig = __DIR__ . '/concept-design-config.php';
if (is_file($serverConfig)) {
    require_once $serverConfig;
}

// Сначала явные переменные окружения, затем значения секретного конфига,
// и только в последнюю очередь безопасные локальные значения по умолчанию.
$serverDbHost = $dbHost ?? null;
$serverDbName = $dbName ?? null;
$serverDbUser = $dbUser ?? null;
$serverDbPass = $dbPass ?? null;

$dbHost = getenv('CONCEPT_DB_HOST');
if ($dbHost === false || $dbHost === '') $dbHost = $serverDbHost ?: 'localhost';

$dbName = getenv('CONCEPT_DB_NAME');
if ($dbName === false || $dbName === '') $dbName = $serverDbName ?: 'u1027561_concept-design';

$dbUser = getenv('CONCEPT_DB_USER');
if ($dbUser === false || $dbUser === '') $dbUser = $serverDbUser ?: 'u1027561_concept-desing';

$dbPass = getenv('CONCEPT_DB_PASSWORD');
if ($dbPass === false) $dbPass = $serverDbPass ?? '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};port=3306;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (Throwable $e) {
    // Не показываем реквизиты БД посетителям. Записываем причину в серверный лог.
    error_log('[CONCEPT DB] Connection failed: ' . $e->getMessage());
    $pdo = null;
}

// ---------- Утилиты ----------
function h($v): string {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

function dbRows(?PDO $pdo, string $sql, array $fallback = []): array {
    if (!$pdo) return $fallback;
    try {
        return $pdo->query($sql)->fetchAll();
    } catch (Throwable $e) {
        return $fallback;
    }
}

function dbRow(?PDO $pdo, string $sql, array $params = []): ?array {
    if (!$pdo) return null;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

// ---------- Глобальные данные ----------
$partners = dbRows($pdo,
    "SELECT * FROM partners WHERE is_active = 1 ORDER BY sort_order ASC",
    [
        ['name' => 'BLUM'],
        ['name' => 'HETTICH'],
        ['name' => 'EGGER'],
        ['name' => 'REHAU'],
        ['name' => 'LAMELUX'],
        ['name' => 'GOLA'],
    ]
);