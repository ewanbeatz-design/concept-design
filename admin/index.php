<?php
require_once __DIR__ . '/includes/auth.php';

if (admin_user()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if (admin_login($username, $password)) {
        redirect('dashboard.php');
    }
    $error = 'Неверный логин или пароль';
}
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Вход — CONCEPT Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
body { font-family: 'Manrope', sans-serif; background: #0f0f0e; min-height: 100vh; display: grid; place-items: center; color: #ecebe6; margin: 0; }
.login-card { background: #1a1a18; border: 1px solid rgba(255,255,255,.08); border-radius: 20px; padding: 48px 40px; width: 100%; max-width: 420px; box-shadow: 0 30px 80px rgba(0,0,0,.5); }
.login-logo-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 12px;
}

.login-logo-img {
    height: 40px;
    width: auto;
    filter: brightness(0) invert(1);
}
.login-sub { font-size: 10px; letter-spacing: .2em; text-transform: uppercase; color: #999; text-align: center; margin-bottom: 32px; }
.form-control { background: #0f0f0e; border: 1px solid rgba(255,255,255,.1); color: #fff; padding: 14px 18px; border-radius: 12px; font-size: 14px; }
.form-control:focus { border-color: #c79d75; background: #0f0f0e; color: #fff; box-shadow: 0 0 0 3px rgba(199,157,109,.15); }
.form-control::placeholder { color: #666; }
.form-label { color: rgba(255,255,255,.6); font-size: 12px; letter-spacing: .1em; text-transform: uppercase; margin-bottom: 8px; }
.btn-login { background: linear-gradient(135deg, #c79d75, #f7e4bb); color: #0a0a0a; border: 0; padding: 14px; border-radius: 12px; font-weight: 700; font-size: 13px; letter-spacing: .1em; text-transform: uppercase; width: 100%; transition: .3s; }
.btn-login:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(199,157,109,.35); }
.alert-danger { background: rgba(185,28,28,.15); border: 1px solid rgba(185,28,28,.3); color: #ffb4b4; border-radius: 10px; padding: 12px 16px; font-size: 13px; }
</style>
</head>
<body>

<form class="login-card" method="post" autocomplete="off">
    <div class="login-logo-wrap">
    <img src="../assets/img/concept-logo.svg" alt="Concept Design" class="login-logo-img">
</div>
<div class="login-sub">панель управления</div>

    <?php if ($error): ?>
        <div class="alert-danger mb-3"><i class="bi bi-exclamation-triangle"></i> <?= h($error) ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label">Логин</label>
        <input type="text" name="username" class="form-control" placeholder="admin" required autofocus>
    </div>

    <div class="mb-4">
        <label class="form-label">Пароль</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn-login">Войти <i class="bi bi-arrow-right"></i></button>
</form>

</body>
</html>