<?php
require_once __DIR__ . '/includes/config.php';

$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');

$innerPages = ['projects.php', 'project.php', 'privacy_policy.php', 'interiors.php', 'interior.php'];
$isInner = in_array($currentPage, $innerPages, true);
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= h($pageTitle ?? 'CONCEPT — дизайн интерьеров и мебель на заказ в Кемерово') ?></title>
<meta name="description" content="<?= h($pageDescription ?? 'Студия авторского дизайна интерьеров и изготовления мебели под ключ в Кемерово.') ?>">
<meta name="keywords" content="<?= h($pageKeywords ?? 'мебель на заказ Кемерово, дизайн интерьера, гардеробная, кухня, шкаф-купе') ?>">

<link rel="canonical" href="https://концепт-дизайн.рф<?= h($_SERVER['REQUEST_URI'] ?? '/') ?>">

<meta property="og:type" content="website">
<meta property="og:title" content="<?= h($pageTitle ?? 'CONCEPT — дизайн интерьеров и мебель на заказ') ?>">
<meta property="og:description" content="<?= h($pageDescription ?? 'Студия авторского дизайна интерьеров и изготовления мебели под ключ в Кемерово.') ?>">
<meta property="og:image" content="https://концепт-дизайн.рф/assets/img/og-image.jpg">
<meta property="og:url" content="https://концепт-дизайн.рф<?= h($_SERVER['REQUEST_URI'] ?? '/') ?>">
<meta property="og:site_name" content="CONCEPT Design">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= h($pageTitle ?? 'CONCEPT — дизайн интерьеров и мебель на заказ') ?>">
<meta name="twitter:description" content="<?= h($pageDescription ?? 'Студия авторского дизайна интерьеров и изготовления мебели под ключ в Кемерово.') ?>">
<meta name="twitter:image" content="https://концепт-дизайн.рф/assets/img/og-image.jpg">

<link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
<link rel="shortcut icon" href="/assets/img/favicon.svg" type="image/x-icon">
<link rel="apple-touch-icon" href="/assets/img/favicon.svg">
<meta name="theme-color" content="#c79d75">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css">
<link rel="stylesheet" href="/assets/css/style.css">

<script>
(function () {
    try {
        var saved = localStorage.getItem('theme');
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var theme = saved || (prefersDark ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
    } catch (e) {}
})();
</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "CONCEPT Design",
  "image": "https://концепт-дизайн.рф/assets/img/og-image.jpg",
  "description": "Студия авторского дизайна интерьеров и изготовления мебели под ключ в Кемерово.",
  "url": "https://концепт-дизайн.рф",
  "telephone": "+7-983-226-47-16",
  "email": "conceptdsign@yandex.ru",
  "priceRange": "₽₽",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Кемерово",
    "addressCountry": "RU"
  },
  "openingHoursSpecification": {
    "@type": "OpeningHoursSpecification",
    "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"],
    "opens": "10:00",
    "closes": "19:00"
  }
}
</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Главная",
      "item": "https://концепт-дизайн.рф/"
    }
    <?php if ($currentPage === 'projects.php'): ?>
    ,{
      "@type": "ListItem",
      "position": 2,
      "name": "Проекты",
      "item": "https://концепт-дизайн.рф/projects"
    }
    <?php elseif ($currentPage === 'project.php' && !empty($project)): ?>
    ,{
      "@type": "ListItem",
      "position": 2,
      "name": "Проекты",
      "item": "https://концепт-дизайн.рф/projects"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "<?= h($project['title']) ?>",
      "item": "https://концепт-дизайн.рф/project/<?= h($project['slug'] ?? $project['id']) ?>"
    }
    <?php elseif ($currentPage === 'interiors.php'): ?>
    ,{
      "@type": "ListItem",
      "position": 2,
      "name": "Интерьеры",
      "item": "https://концепт-дизайн.рф/interiors"
    }
    <?php elseif ($currentPage === 'interior.php' && !empty($interior)): ?>
    ,{
      "@type": "ListItem",
      "position": 2,
      "name": "Интерьеры",
      "item": "https://концепт-дизайн.рф/interiors"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "<?= h($interior['title']) ?>",
      "item": "https://концепт-дизайн.рф/interior/<?= h($interior['slug'] ?? $interior['id']) ?>"
    }
    <?php elseif ($currentPage === 'privacy_policy.php'): ?>
    ,{
      "@type": "ListItem",
      "position": 2,
      "name": "Политика конфиденциальности",
      "item": "https://концепт-дизайн.рф/privacy"
    }
    <?php endif; ?>
  ]
}
</script>

</head>
<body class="<?= $isInner ? 'page-inner' : 'page-home' ?>">

<div class="noise"></div>

<header class="header" id="siteHeader">
    <div class="container">
        <a href="/index.php" class="logo">
            <img src="/assets/img/concept-logo.svg" alt="Concept Design" class="logo__img">
        </a>

        <nav class="nav">
            <a href="/projects.php">Проекты</a>
            <a href="/index.php#furniture">Мебель</a>
            <a href="/index.php#process">Процесс</a>
            <a href="/interiors.php">Интерьеры</a>
            <a href="/index.php#configurator">Рассчитать</a>
            <a href="/index.php#contact">Контакты</a>
        </nav>

        <div class="header-right">
            <a href="tel:+79832264716" class="header-phone">+7 983 226-47-16</a>

            <button type="button" class="theme-toggle" id="themeToggle" aria-label="Переключить тему">
                <i class="bi bi-sun-fill theme-toggle__sun"></i>
                <i class="bi bi-moon-stars-fill theme-toggle__moon"></i>
            </button>

            <button class="header-btn d-none d-lg-inline-flex" data-bs-toggle="modal" data-bs-target="#contactModal">
                Обсудить проект
                <i class="bi bi-arrow-up-right"></i>
            </button>

            <button class="circle-btn" id="menuToggle" aria-label="Открыть меню">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>
</header>

<main id="top">