<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/json; charset=utf-8');

function respond(bool $ok, array $extra = []): void {
    echo json_encode(array_merge(['success' => $ok], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

$project_id = (int)($_POST['project_id'] ?? 0);
$action     = $_POST['action'] ?? 'like';

if (!$project_id || !$pdo) {
    respond(false, ['error' => 'Неверный запрос']);
}

$user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
$likeKey = 'project_like_' . $project_id . '_' . md5($user_ip);
$hasLiked = !empty($_SESSION[$likeKey]);

try {
    if ($action === 'like' && !$hasLiked) {
        $pdo->prepare("UPDATE projects SET likes = likes + 1 WHERE id = ?")->execute([$project_id]);
        $_SESSION[$likeKey] = true;
        $liked = true;
    } elseif ($action === 'unlike' && $hasLiked) {
        $pdo->prepare("UPDATE projects SET likes = GREATEST(likes - 1, 0) WHERE id = ?")->execute([$project_id]);
        unset($_SESSION[$likeKey]);
        $liked = false;
    } else {
        $liked = $hasLiked;
    }

    $row = dbRow($pdo, "SELECT likes FROM projects WHERE id = ?", [$project_id]);
    respond(true, [
        'liked' => $liked,
        'likes' => (int)($row['likes'] ?? 0),
    ]);
} catch (Throwable $e) {
    respond(false, ['error' => 'Ошибка сервера']);
}