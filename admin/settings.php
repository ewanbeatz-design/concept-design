<?php
$pageTitle = 'Настройки';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'change_password') {
        $user = admin_user();
        $current = (string)($_POST['current_password'] ?? '');
        $new     = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$user['id']]);
        $dbPass = $stmt->fetchColumn();

        if ($dbPass !== $current) {
            $error = 'Текущий пароль неверен';
        } elseif (strlen($new) < 6) {
            $error = 'Новый пароль минимум 6 символов';
        } elseif ($new !== $confirm) {
            $error = 'Пароли не совпадают';
        } else {
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$new, $user['id']]);
            $success = 'Пароль обновлён';
        }
    }
}
?>

<?php if ($success): ?>
    <div class="admin-alert admin-alert--success"><i class="bi bi-check2-circle"></i> <?= h($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="admin-alert admin-alert--danger"><i class="bi bi-exclamation-triangle"></i> <?= h($error) ?></div>
<?php endif; ?>

<div class="admin-grid-2">

    <div class="admin-card">
        <h2 class="admin-card-title">Смена пароля</h2>

        <form method="post" class="admin-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="change_password">

            <div class="form-row">
                <label>Текущий пароль</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-row">
                <label>Новый пароль</label>
                <input type="password" name="new_password" required minlength="6">
            </div>
            <div class="form-row">
                <label>Повторите</label>
                <input type="password" name="confirm_password" required minlength="6">
            </div>

            <div class="form-actions">
                <button type="submit" class="admin-btn admin-btn--primary">
                    <i class="bi bi-shield-lock"></i> Обновить пароль
                </button>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <h2 class="admin-card-title">Информация</h2>

        <div class="meta-list">
            <div class="meta-row">
                <span>PHP-версия</span>
                <strong><?= PHP_VERSION ?></strong>
            </div>
            <div class="meta-row">
                <span>База данных</span>
                <strong><?= $pdo ? 'подключена' : 'нет связи' ?></strong>
            </div>
            <div class="meta-row">
                <span>Пользователь</span>
                <strong><?= h(admin_user()['username']) ?></strong>
            </div>
            <div class="meta-row">
                <span>Последний вход</span>
                <strong><?= date('d.m.Y H:i') ?></strong>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>