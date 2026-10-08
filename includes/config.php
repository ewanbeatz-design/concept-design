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
$pdo = null;
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=concept_design;port=3306;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (Throwable $e) {
    $pdo = null;
}

// ---------- Утилиты ----------z
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