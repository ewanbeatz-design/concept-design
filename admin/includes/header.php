<?php
require_once __DIR__ . '/auth.php';
admin_check();

$user    = admin_user();
$current = basename($_SERVER['PHP_SELF']);

$newCount = 0;
if ($pdo) {
    try {
        $newCount = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE status='new'")->fetchColumn();
    } catch (Throwable $e) {
        $newCount = 0;
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle ?? 'Админка') ?> — CONCEPT Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css">
<script>window.__csrf = '<?= h(csrf_token()) ?>';</script>
<script>(function(){try{var s=localStorage.getItem('admin_theme'),p=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches,t=s||(p?'dark':'dark');document.documentElement.setAttribute('data-admin-theme',t)}catch(e){}})();</script>
</head>
<body>
<div class="admin-shell">
<aside class="admin-sidebar" id="adminSidebar">
<a href="dashboard.php" class="admin-logo"><img src="../assets/img/concept-logo.svg" alt="Concept Design" class="admin-logo__img"></a>
<nav class="admin-nav">
<a href="dashboard.php" class="<?= $current==='dashboard.php'?'active':'' ?>"><i class="bi bi-grid-1x2"></i><span>Дашборд</span></a>
<a href="leads.php" class="<?= in_array($current,['leads.php','lead.php'],true)?'active':'' ?>"><i class="bi bi-inbox"></i><span>Заявки</span><?php if($newCount>0):?><span class="badge-new"><?=$newCount?></span><?php endif;?></a>
<a href="projects.php" class="<?= in_array($current,['projects.php','project-edit.php'],true)?'active':'' ?>"><i class="bi bi-images"></i><span>Проекты</span></a>
<a href="interiors-list.php" class="<?= in_array($current,['interiors-list.php','interiors-edit.php'],true)?'active':'' ?>"><i class="bi bi-house-heart"></i><span>Интерьеры</span></a>
<a href="estimates.php" class="<?= in_array($current,['estimates.php','estimate-edit.php'],true)?'active':'' ?>"><i class="bi bi-calculator"></i><span>Сметы</span></a>\n<a href="quick-estimate.php" class="<?= $current==='quick-estimate.php'?'active':'' ?>"><i class="bi bi-box-seam"></i><span>Каталог</span></a>
<a href="settings.php" class="<?= $current==='settings.php'?'active':'' ?>"><i class="bi bi-gear"></i><span>Настройки</span></a>
</nav>
<div class="admin-user"><div class="admin-user-info"><div class="admin-user-avatar"><?=mb_strtoupper(mb_substr($user['username']??'A',0,1))?></div><div><strong><?=h($user['username']??'admin')?></strong><small>администратор</small></div></div><a href="logout.php" class="admin-logout" title="Выйти"><i class="bi bi-box-arrow-right"></i></a></div>
</aside>
<div class="admin-main">
<header class="admin-topbar"><button class="admin-menu-toggle" id="adminMenuToggle" aria-label="Меню"><i class="bi bi-list"></i></button><h1 class="admin-page-title"><?=h($pageTitle??'Админка')?></h1><div class="admin-topbar-actions"><button type="button" class="admin-theme-toggle" id="adminThemeToggle" aria-label="Переключить тему"><i class="bi bi-sun-fill admin-theme-toggle__sun"></i><i class="bi bi-moon-stars-fill admin-theme-toggle__moon"></i></button><a href="../" target="_blank" class="admin-topbar-btn"><i class="bi bi-box-arrow-up-right"></i><span>На сайт</span></a></div></header>
<main class="admin-content">