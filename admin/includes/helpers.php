<?php
declare(strict_types=1);

if (!function_exists('h')) {
    function h($v): string {
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('fmt_money')) {
    function fmt_money($n): string {
        return number_format((float)$n, 0, '.', ' ') . ' ₽';
    }
}

if (!function_exists('fmt_date')) {
    function fmt_date(?string $date, string $format = 'd.m.Y H:i'): string {
        if (!$date) return '—';
        try {
            return (new DateTime($date))->format($format);
        } catch (Throwable $e) {
            return '—';
        }
    }
}

if (!function_exists('slug')) {
    function slug(string $str): string {
        $str = mb_strtolower($str);
        $str = preg_replace('/[^a-z0-9а-яё\s-]/u', '', $str);
        $str = preg_replace('/[\s-]+/', '-', $str);
        return trim($str, '-');
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null) {
        if ($message !== null) {
            $_SESSION['flash'][$key] = $message;
            return null;
        }
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="_csrf" value="' . h(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_check')) {
    function csrf_check(): void {
        $token = $_POST['_csrf'] ?? '';
        if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
            http_response_code(419);
            exit('CSRF token mismatch');
        }
    }
}

if (!function_exists('upload_image')) {
    function upload_image(array $file, string $subdir = 'projects', array $allowed = ['jpg','jpeg','png','webp','gif','svg']): ?string {
        if (empty($file['tmp_name']) || ($file['error'] ?? 0) !== UPLOAD_ERR_OK) return null;

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) return null;

        $uploadDir = __DIR__ . '/../../assets/uploads/' . $subdir . '/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $filename = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $target = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) return null;

        return 'assets/uploads/' . $subdir . '/' . $filename;
    }
}

if (!function_exists('db_insert')) {
    function db_insert(PDO $pdo, string $table, array $data): int {
        $cols = array_keys($data);
        $placeholders = array_map(fn($c) => ':' . $c, $cols);
        $sql = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        return (int)$pdo->lastInsertId();
    }
}

if (!function_exists('db_update')) {
    function db_update(PDO $pdo, string $table, array $data, string $where, array $whereParams): void {
        $set = [];
        foreach (array_keys($data) as $c) $set[] = "`$c` = :$c";
        $sql = "UPDATE `$table` SET " . implode(',', $set) . " WHERE $where";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge($data, $whereParams));
    }
}

if (!function_exists('status_label')) {
    function status_label(string $status): string {
        return match ($status) {
            'new'       => 'Новая',
            'contacted' => 'В работе',
            'completed' => 'Завершена',
            'cancelled' => 'Отменена',
            default     => $status,
        };
    }
}