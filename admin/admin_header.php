<?php
// Проверка авторизации (должна быть в каждом файле до подключения header)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}
?>
<!-- Header из index.php для админки -->
<div class="header bg-dark sticky transparent-light">
    <div class="container">
        <!-- Logo -->
        <div class="header-logo">
            <h3><a href="dashboard.php"><img alt="logo" src="../images/concept-logo.svg"></a></h3>
        </div>
        <!-- Fullscreen Menu Toggle -->
        <div class="header-menu">
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">ГЛАВНАЯ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'projects.php' ? 'active' : ''; ?>" href="projects.php">ПРОЕКТЫ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'partners.php' ? 'active' : ''; ?>" href="partners.php">ПАРТНЕРЫ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'leads.php' ? 'active' : ''; ?>" href="leads.php">ЗАЯВКИ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fs-5 d-block d-lg-none d-lg-block d-lg-none" href="#">+7-983-226-47-16</a>
                </li>
                <li class="">
                    <a class="nav-link text-center button btn btn-warning button-xl button-rounded d-block d-lg-none d-lg-block d-lg-none mt-2 text-dark" href="logout.php">Выйти</a>
                </li>
                <li>
                    <div class="col-12 col-md-6 text-white mt-5 d-block d-lg-none">
                        <h5 class="fw-normal text-white">Админ-панель</h5>
                        <ul class="list-unstyled">
                            <li>Управление проектами</li>
                            <li><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
        <div class="header-menu-extra d-lg-block d-none">
            <sub class="text-white lh-1">Админ-панель</sub>
            <a class="nav-link align-middle d-lg-block d-md-none d-none" href="#"> <p class="h5 m-0"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></p></a>
            <sup class="text-white lh-1"><i class="fa-solid fa-circle fa-2xs" style="color: #1ec412;"></i> Online</sup>
        </div>
        <div class="header-menu-extra d-lg-block d-none">
            <a href="logout.php" class="button button-md button-rounded btn btn-warning d-md-none d-lg-block">Выйти</a>
        </div>
        <button class="header-toggle"><span></span></button>
    </div>
    <!-- end container -->
</div>
<!-- end Header -->
<div class="header-placeholder"></div>