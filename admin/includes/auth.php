<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('admin_user')) {
    function admin_user(): ?array {
        return $_SESSION['admin_user'] ?? null;
    }
}

if (!function_exists('admin_check')) {
    function admin_check(): void {
        if (!admin_user()) {
            redirect('index.php');
        }
    }
}

if (!function_exists('admin_login')) {
    function admin_login(string $username, string $password): bool {
        global $pdo;
        if (!$pdo) return false;

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) return false;

        if ($password !== $user['password']) return false;

        $_SESSION['admin_user'] = [
            'id'       => (int)$user['id'],
            'username' => $user['username'],
            'email'    => $user['email'] ?? null,
        ];
        return true;
    }
}

if (!function_exists('admin_logout')) {
    function admin_logout(): void {
        unset($_SESSION['admin_user']);
        session_destroy();
    }
}