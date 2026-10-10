<?php
require_once __DIR__ . '/includes/config.php';

$pageTitle       = 'CONCEPT — дизайн интерьеров и мебель на заказ в Кемерово';
$pageDescription = 'Студия авторского дизайна интерьеров и изготовления мебели под ключ в Кемерово. Гардеробные, кухни, шкафы-купе на заказ.';

// Проекты (fallback, если БД пустая)
$projects = dbRows($pdo,
    "SELECT * FROM projects WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC LIMIT 8",
    [
        ['id' => 1, 'title' => 'Гардеробная с подсветкой', 'description' => 'Встроенная гардеробная с системой хранения', 'image_url' => 'assets/img/projects-1.jpg', 'type' => 'FURNITURE'],
        ['id' => 2, 'title' => 'Кухня-гостиная',            'description' => 'Объединённое пространство с кухонным островом', 'image_url' => 'assets/img/projects-2.jpg', 'type' => 'INTERIOR'],
        ['id' => 3, 'title' => 'Шкаф-купе в спальню',       'description' => 'Шкаф-купе с зеркальными фасадами',            'image_url' => 'assets/img/projects-3.jpg', 'type' => 'FURNITURE'],
        ['id' => 4, 'title' => 'Квартира с характером',     'description' => 'Тёплый минимализм, натуральный шпон и камень','image_url' => 'assets/img/projects-4.jpg', 'type' => 'INTERIOR'],
    ]
);

// Общая галерея читает фотографии только из assets/uploads/all. К БД не обращается.
$workPhotos = [];
$uploadsRoot = __DIR__ . '/assets/uploads/all';
$uploadsReal = realpath($uploadsRoot);
if ($uploadsReal !== false && is_dir($uploadsRoot)) {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif'];
    $directory = new RecursiveDirectoryIterator($uploadsReal, FilesystemIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::LEAVES_ONLY);
    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile() || $fileInfo->isLink()) continue;
        if (!in_array(strtolower($fileInfo->getExtension()), $allowedExtensions, true)) continue;
        $realFile = $fileInfo->getRealPath();
        if ($realFile === false || strpos($realFile, $uploadsReal . DIRECTORY_SEPARATOR) !== 0) continue;
        $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', substr($realFile, strlen(__DIR__) + 1));
        $parentName = basename(dirname($realFile));
        $title = preg_match('/^project-(\d+)$/i', $parentName, $matches)
            ? 'Реализованная работа — проект ' . $matches[1]
            : 'Реализованная работа CONCEPT Design';
        $workPhotos[] = ['src' => $relativePath, 'title' => $title];
    }
    usort($workPhotos, static fn($a, $b) => strnatcasecmp($a['src'], $b['src']));
}

include __DIR__ . '/header.php';
?>

<!-- ================= HERO ================= -->
<section class="hero" data-aos="fade-in">
    <div class="hero-media"><img src="assets/img/hero.webp" alt="Интерьер Concept Design"></div>
    <div class="hero-shade"></div>
    <div class="container hero-inner">
        <div class="hero-meta">
            <span>01 / 08</span>
            <span>КЕМЕРОВО · 2026</span>
        </div>

        <div class="hero-copy" data-aos="fade-up" data-aos-delay="200">
            <p class="eyebrow">CONCEPT DESIGN — STUDIO</p>
            <h1>Дизайнерская<br><em>мебель</em> и интерьеры<br>под ключ</h1>
            <p class="hero-lead">Создаём пространство целиком: от первой идеи и 3D-визуализации до производства, монтажа и финального света.</p>

            <div class="hero-actions">
                <button type="button" class="btn-solid" data-bs-toggle="modal" data-bs-target="#project-quiz">
                    Рассчитать проект
                    <i class="bi bi-calculator"></i>
                </button>
                <a href="#contact" class="text-link">
                    Обсудить проект <i class="bi bi-arrow-up-right"></i>
                </a>
                <a href="#projects" class="text-link">
                    Смотреть проекты <span>↘</span>
                </a>
            </div>
        </div>

        <div class="hero-bottom">
            <span>SCROLL TO EXPLORE</span>
            <span class="scroll-line"></span>
            <span>CONCEPT / 26</span>
        </div>
    </div>
</section>

<!-- ================= MANIFESTO ================= -->
<section class="section section-light manifesto" data-aos="fade-up">
    <div class="container">
        <span class="kicker">00 / Философия</span>

        <div class="manifesto-grid">
            <h2 data-aos="fade-right">Мы не делаем<br><span>просто мебель.</span></h2>
            <div class="manifesto-copy" data-aos="fade-left" data-aos-delay="100">
                <p class="big-copy">Мы проектируем среду, в которой каждая линия, материал и функция работают на одно ощущение — <strong>«это моё».</strong></p>
                <p>Интерьер и мебель создаются как единая система. Без случайных деталей, без компромиссов между эстетикой и практичностью.</p>
                <a class="text-link dark" href="#contact">Познакомиться со студией <span>↗</span></a>
            </div>
        </div>

        <div class="stats" data-aos="fade-up" data-aos-delay="150">
            <div><strong>12+</strong><span>лет в мебели и дизайне</span></div>
            <div><strong>150+</strong><span>реализованных проектов</span></div>
            <div><strong>100%</strong><span>индивидуальная разработка</span></div>
            <div><strong>01</strong><span>команда от идеи до монтажа</span></div>
        </div>
    </div>
</section>

<!-- ================= PROJECTS ================= -->
<section class="section section-light projects" id="projects" data-aos="fade-up">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">01 / Selected work</span>
                <h2 class="h-display">Пространства,<br><em>в которых хочется жить</em></h2>
            </div>
            <div class="section-head__action">
                <a href="projects.php" class="btn btn--outline btn--sm">
                    Все проекты
                    <i class="bi bi-arrow-up-right"></i>
                </a>
            </div>
        </div>

        <div class="owl-carousel owl-theme projects-carousel">
            <?php foreach ($projects as $i => $p): ?>
            <div class="item">
                <a href="project.php?id=<?= (int)$p['id'] ?>" class="project-card-link">
                    <article class="project-card">
                        <div class="project-image">
                            <img src="<?= h($p['image_url'] ?: 'assets/img/placeholder.jpg') ?>"
                                 alt="<?= h($p['title']) ?>"
                                 loading="lazy">
                        </div>
                        <div class="project-overlay">
                            <span>0<?= $i + 1 ?> — <?= h($p['type'] ?? 'PROJECT') ?></span>
                            <h3><?= h($p['title']) ?></h3>
                            <p><?= h($p['description']) ?></p>
                            <i class="bi bi-arrow-up-right"></i>
                        </div>
                    </article>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ================= FILESYSTEM WORK GALLERY ================= -->
<?php if (!empty($workPhotos)): ?>
<section class="work-gallery section section-light" id="work-gallery" data-aos="fade-up">
    <div class="container">
        <div class="section-head work-gallery__head">
            <div>
                <span class="kicker">01.1 / Реализованные работы</span>
                <h2 class="h-display">В деталях.<br><em>Реальные работы</em></h2>
            </div>
            <p class="section-head__text">
                Фотографии мебели и интерьеров CONCEPT Design.
                Выберите миниатюру справа, чтобы открыть фотографию.
            </p>
        </div>
        <div class="work-gallery__layout">
            <div class="work-gallery__viewer">
                <div class="owl-carousel owl-theme work-gallery__carousel" aria-label="Фотографии реализованных работ">
                    <?php foreach ($workPhotos as $i => $photo): ?>
                    <div class="work-gallery__slide">
                        <a class="work-gallery__item" href="<?= h($photo['src']) ?>"
                           data-fancybox="all-work-photos" data-caption="<?= h($photo['title']) ?>"
                           aria-label="Открыть фотографию в полном размере: <?= h($photo['title']) ?>">
                            <img src="<?= h($photo['src']) ?>"
                                 alt="<?= h($photo['title']) ?> — фото <?= $i + 1 ?>"
                                 loading="<?= $i === 0 ? 'eager' : 'lazy' ?>" decoding="async">
                            <span class="work-gallery__caption"><?= h($photo['title']) ?></span>
                            <span class="work-gallery__zoom" aria-hidden="true"><i class="bi bi-arrows-angle-expand"></i></span>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="work-gallery__meta">
                    <span>CONCEPT DESIGN / PORTFOLIO</span>
                    <span class="work-gallery__counter"><b id="workGalleryCurrent">01</b> / <?= str_pad((string)count($workPhotos), 2, '0', STR_PAD_LEFT) ?></span>
                </div>
            </div>
            <div class="work-gallery__thumbs" role="list" aria-label="Миниатюры фотографий">
                <?php foreach ($workPhotos as $i => $photo): ?>
                <button class="work-gallery__thumb<?= $i === 0 ? ' is-active' : '' ?>"
                        type="button" role="listitem" data-gallery-to="<?= $i ?>"
                        aria-label="Показать фотографию <?= $i + 1 ?>"
                        aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>">
                    <img src="<?= h($photo['src']) ?>" alt="" loading="lazy" decoding="async">
                    <span><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="work-gallery__footer">
            <span>ИНТЕРЬЕРЫ · МЕБЕЛЬ · РЕАЛИЗАЦИЯ</span>
            <a href="projects.php" class="text-link dark">О проектах подробнее <span>↗</span></a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ================= MARQUEE ================= -->
<div class="marquee" aria-hidden="true">
    <div>
        <span><em>CONCEPT</em> DESIGN</span>
        <span>Кухни</span>
        <span>Гардеробные</span>
        <span>Интерьеры</span>
        <span>Мебель под ключ</span>
        <span>Столешницы</span>
        <span>Шкафы-купе</span>
        <span>Детская мебель</span>
        <span><em>CONCEPT</em> DESIGN</span>
        <span>Кухни</span>
        <span>Гардеробные</span>
        <span>Интерьеры</span>
        <span>Мебель под ключ</span>
        <span>Столешницы</span>
        <span>Шкафы-купе</span>
        <span>Детская мебель</span>
    </div>
</div>

<!-- ================= FURNITURE ================= -->
<section class="split-dark" id="furniture" data-aos="fade-up">
    <div class="split-image">
        <img src="assets/img/materials.jpg" alt="Материалы и мебель">
    </div>
    <div class="split-content" data-aos="fade-left" data-aos-delay="100">
        <span class="kicker">02 / Furniture as architecture</span>
        <h2 class="h-display">Мебель —<br><em>это архитектура.</em></h2>
        <p>Мы собираем интерьер вокруг мебели, а не наоборот. Фасады, шпон, камень, стекло, фурнитура и свет становятся частью одной композиции.</p>

        <div class="material-list">
            <span>МДФ / ШПОН</span><span>КАМЕНЬ / КВАРЦ</span>
            <span>BLUM / HETTICH</span><span>LED / GOLA</span>
        </div>

        <a href="#configurator" class="text-link light">Выбрать материалы <span>↗</span></a>
    </div>
</section>

<!-- ================= INTERIOR DESIGN SERVICE ================= -->
<section class="section section-light interior-design" id="interior-design" data-aos="fade-up">
    <div class="container">

        <div class="section-head">
            <div>
                <span class="kicker">03 / Дизайн интерьера</span>
                <h2 class="h-display">Проект,<br><em>в котором всё продумано</em></h2>
            </div>
            <p class="section-head__text">
                Разрабатываем дизайн-проект целиком: от планировки и 3D-визуализации до рабочей
                документации и подбора материалов. Дальше — реализуем и контролируем.
            </p>
        </div>

        <!-- Форматы работы -->
        <div class="design-formats" data-aos="fade-up">

            <article class="design-format">
                <span class="design-format__num">01</span>
                <h3 class="design-format__title">Эскизный проект</h3>
                <p class="design-format__desc">
                    Быстрый старт: планировочные решения, стилевая концепция, ключевые
                    рекомендации по отделке и мебели.
                </p>
                <ul class="design-format__list">
                    <li>2 планировочных решения</li>
                    <li>Мудборд и коллаж материалов</li>
                    <li>Рекомендации по бюджету</li>
                </ul>
                <div class="design-format__price">
                    от <strong>1 450 ₽</strong> / м²
                </div>
            </article>

            <article class="design-format design-format--hit">
                <span class="design-format__badge">Популярное</span>
                <span class="design-format__num">02</span>
                <h3 class="design-format__title">Полный дизайн-проект</h3>
                <p class="design-format__desc">
                    Комплексная разработка: планировка, визуализации всех помещений,
                    рабочие чертежи и спецификация отделки.
                </p>
                <ul class="design-format__list">
                    <li>3 планировочных решения</li>
                    <li>3D-визуализации каждой комнаты</li>
                    <li>Полный комплект рабочих чертежей</li>
                    <li>Спецификация материалов и мебели</li>
                </ul>
                <div class="design-format__price">
                    от <strong>3 500 ₽</strong> / м²
                </div>
            </article>

            <article class="design-format">
                <span class="design-format__num">03</span>
                <h3 class="design-format__title">Проект + реализация</h3>
                <p class="design-format__desc">
                    Полный цикл: от идеи и чертежей до готового интерьера с мебелью,
                    светом и декором.
                </p>
                <ul class="design-format__list">
                    <li>Всё из полного проекта</li>
                    <li>Подбор материалов и мебели</li>
                    <li>Авторский надзор</li>
                    <li>Сдача объекта под ключ</li>
                </ul>
                <div class="design-format__price">
                    от <strong>3 800 ₽</strong> / м²
                </div>
            </article>

        </div>

        <!-- Что входит в проект -->
        <div class="design-includes" data-aos="fade-up" data-aos-delay="100">
            <div class="design-includes__head">
                <h3 class="design-includes__title">Что входит в дизайн-проект</h3>
            </div>

            <div class="design-includes__grid">
                <div class="design-include">
                    <i class="bi bi-rulers"></i>
                    <span>Обмерный план</span>
                </div>
                <div class="design-include">
                    <i class="bi bi-grid-3x3"></i>
                    <span>Планировочные решения</span>
                </div>
                <div class="design-include">
                    <i class="bi bi-bounding-box"></i>
                    <span>3D-визуализации</span>
                </div>
                <div class="design-include">
                    <i class="bi bi-file-earmark-ruled"></i>
                    <span>Рабочие чертежи</span>
                </div>
                <div class="design-include">
                    <i class="bi bi-list-check"></i>
                    <span>Спецификации</span>
                </div>
                <div class="design-include">
                    <i class="bi bi-lightbulb"></i>
                    <span>Схемы освещения</span>
                </div>
                <div class="design-include">
                    <i class="bi bi-droplet"></i>
                    <span>Разводка сантехники</span>
                </div>
                <div class="design-include">
                    <i class="bi bi-plug"></i>
                    <span>Схемы электрики</span>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="design-cta" data-aos="fade-up" data-aos-delay="150">
            <div class="design-cta__content">
                <h3>Не знаете, с чего начать?</h3>
                <p>Оставьте заявку — обсудим задачу и подберём формат проекта под ваш бюджет.</p>
            </div>
            <button class="btn gold-btn" data-bs-toggle="modal" data-bs-target="#contactModal">
                Обсудить проект
                <i class="bi bi-arrow-up-right"></i>
            </button>
        </div>

    </div>
</section>

<!-- ================= WHY US ================= -->
<section class="section section-dark why-us" id="why-us" data-aos="fade-up">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">04 / Почему мы</span>
                <h2 class="h-display">Почему нам<br><em>доверяют</em></h2>
            </div>
            <p class="section-head__text">
                12 лет на рынке, собственное производство и полный цикл — от эскиза до ключей.
            </p>
        </div>

        <div class="why-grid">
            <article class="why-card" data-aos="fade-up">
                <span class="why-card__num">01</span>
                <i class="bi bi-house-gear why-card__icon"></i>
                <h3 class="why-card__title">Собственное производство</h3>
                <p class="why-card__text">
                    Не посредники. Всё делаем на своём оборудовании — станки ЧПУ,
                    автоматическая кромка, присадка по координатной сетке.
                </p>
            </article>

            <article class="why-card" data-aos="fade-up" data-aos-delay="100">
                <span class="why-card__num">02</span>
                <i class="bi bi-people why-card__icon"></i>
                <h3 class="why-card__title">Одна команда</h3>
                <p class="why-card__text">
                    Дизайнер, замерщик, конструктор, монтажник — все свои.
                    Один ответственный за результат от начала до конца.
                </p>
            </article>

            <article class="why-card" data-aos="fade-up" data-aos-delay="200">
                <span class="why-card__num">03</span>
                <i class="bi bi-shield-check why-card__icon"></i>
                <h3 class="why-card__title">Гарантия на всё</h3>
                <p class="why-card__text">
                    Даём письменную гарантию на мебель, фурнитуру и монтаж.
                    Если что-то не так — приедем и починим за свой счёт.
                </p>
            </article>

            <article class="why-card" data-aos="fade-up" data-aos-delay="300">
                <span class="why-card__num">04</span>
                <i class="bi bi-wallet2 why-card__icon"></i>
                <h3 class="why-card__title">Прозрачная смета</h3>
                <p class="why-card__text">
                    Фиксируем стоимость в договоре. Никаких «внезапно подорожало» —
                    все доп. работы только по вашему согласию.
                </p>
            </article>
        </div>
    </div>
</section>

<!-- ================= TEAM ================= -->
<section class="section section-light team" id="team" data-aos="fade-up">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">05 / Команда</span>
                <h2 class="h-display">Люди,<br><em>которые делают</em></h2>
            </div>
            <p class="section-head__text">
                Небольшая команда без «прослоек» — вы всегда знаете, кто отвечает за ваш проект.
            </p>
        </div>

        <div class="team-grid">
            <article class="team-card" data-aos="fade-up">
                <img class="team-card__photo" src="assets/img/team/designer.jpg" alt="Анна Смирнова — ведущий дизайнер">
                <h3 class="team-card__name">Анна Смирнова</h3>
                <p class="team-card__role">Ведущий дизайнер</p>
                <p class="team-card__text">
                    10 лет в интерьерах. Специализация — тёплый минимализм, японские мотивы, свет.
                </p>
            </article>

            <article class="team-card" data-aos="fade-up" data-aos-delay="100">
                <img class="team-card__photo" src="assets/img/team/constructor.jpg" alt="Дмитрий Ковалёв — конструктор">
                <h3 class="team-card__name">Дмитрий Ковалёв</h3>
                <p class="team-card__role">Конструктор</p>
                <p class="team-card__text">
                    Отвечает за рабочие чертежи и присадку. Точность 0,1 мм — его стандарт.
                </p>
            </article>

            <article class="team-card" data-aos="fade-up" data-aos-delay="200">
                <img class="team-card__photo" src="assets/img/team/manager.jpg" alt="Мария Волкова — менеджер проектов">
                <h3 class="team-card__name">Мария Волкова</h3>
                <p class="team-card__role">Менеджер проектов</p>
                <p class="team-card__text">
                    Ваш единственный контакт. Согласует сроки, бюджет и все работы на объекте.
                </p>
            </article>

            <article class="team-card" data-aos="fade-up" data-aos-delay="300">
                <img class="team-card__photo" src="assets/img/team/master.jpg" alt="Игорь Петров — бригадир монтажа">
                <h3 class="team-card__name">Игорь Петров</h3>
                <p class="team-card__role">Бригадир монтажа</p>
                <p class="team-card__text">
                    15 лет на монтаже. Устанавливает кухни и шкафы так, что зазоров не видно вообще.
                </p>
            </article>
        </div>
    </div>
</section>

<!-- ================= INTERIORS ================= -->
<?php
$interiors = dbRows($pdo,
    "SELECT * FROM interiors WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC LIMIT 3"
);
?>

<?php if (!empty($interiors)): ?>
<section class="section section-light interior-gallery" id="interiors-gallery" data-aos="fade-up">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">06 / Дизайн интерьера</span>
                <h2 class="h-display">Создаём проекты<br><em>для гармонии жизни</em></h2>
            </div>
            <a href="interiors.php" class="btn btn--outline btn--sm">
                Все интерьеры
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="interiors-grid">
            <?php foreach ($interiors as $i => $item): ?>
            <a href="interiors.php?id=<?= (int)$item['id'] ?>"
               class="interior-card"
               data-aos="fade-up"
               data-aos-delay="<?= $i * 100 ?>">

                <div class="interior-card__image image-placeholder"
                     data-image="<?= h($item['image_url']) ?>">
                    <div class="interior-card__overlay">
                        <span class="interior-card__type"><?= h($item['type'] ?? 'Интерьер') ?></span>
                        <h3 class="interior-card__title"><?= h($item['title']) ?></h3>
                    </div>
                </div>

                <div class="interior-card__info">
                    <p class="interior-card__desc"><?= h($item['description']) ?></p>

                    <div class="interior-card__meta">
                        <?php if (!empty($item['area'])): ?>
                            <span><?= h($item['area']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($item['style'])): ?>
                            <span><?= h($item['style']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>



<!-- ================= FACADES ================= -->
<?php
$facadeCollections = dbRows($pdo,
    "SELECT * FROM facade_collections WHERE is_active = 1 ORDER BY sort_order ASC",
    [
        ['id' => 1, 'title' => 'Коллекция фасадов / столешниц', 'subtitle' => 'в кромке ABS',  'cover_image' => 'assets/img/facade-abs.jpg'],
        ['id' => 2, 'title' => 'Коллекция фасадов',              'subtitle' => 'в плёнке PVC',  'cover_image' => 'assets/img/facade-pvc.jpg'],
        ['id' => 3, 'title' => 'Коллекция фасадов',              'subtitle' => 'PREMIER MATT',  'cover_image' => 'assets/img/facade-premier.jpg'],
    ]
);
?>

<?php if (!empty($facadeCollections)): ?>
<section class="section section-dark facades" id="facades" data-aos="fade-up">
    <div class="container">

        <div class="facades-head">
            <div class="facades-line"></div>
            <h2 class="facades-title">Коллекции фасадов 2026</h2>
        </div>

        <div class="facades-grid">
            <?php foreach ($facadeCollections as $i => $col): ?>
            <a href="facades.php?id=<?= (int)$col['id'] ?>"
               class="facade-card"
               data-aos="fade-up"
               data-aos-delay="<?= $i * 100 ?>">
                <div class="facade-card__caption">
                    <span class="facade-card__title"><?= h($col['title']) ?></span>
                    <?php if (!empty($col['subtitle'])): ?>
                        <span class="facade-card__subtitle"><?= h($col['subtitle']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="facade-card__image">
                    <img src="<?= h($col['cover_image']) ?>"
                         alt="<?= h($col['title'] . ' ' . ($col['subtitle'] ?? '')) ?>"
                         loading="lazy">
                    <div class="facade-card__overlay"></div>
                    <span class="facade-card__arrow"><i class="bi bi-arrow-up-right"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ================= PRODUCTION ================= -->
<section class="section section-light production" id="production" data-aos="fade-up">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">02.5 / Производство</span>
                <h2 class="h-display">Точность<br><em>на каждом этапе</em></h2>
            </div>
            <p class="section-head__text">
                Мы не отдаём заказы «на сторону». Всё делаем на своём производстве — от раскроя до упаковки.
            </p>
        </div>

        <div class="production-grid">

            <article class="production-card" data-aos="fade-up">
                <div class="production-card__num">01</div>
                <div class="production-card__icon">
                    <i class="bi bi-bounding-box-circles"></i>
                </div>
                <h3 class="production-card__title">Раскрой на станке с ЧПУ</h3>
                <p class="production-card__text">
                    Все детали вырезаются на фрезерном станке с ЧПУ по цифровой карте проекта.
                    Точность — до 0,1 мм. Никаких «на глазок» и ручной разметки: каждая деталь
                    идеально повторяет чертёж, а все крепёжные отверстия и пазы уже заложены в программу.
                </p>
                <ul class="production-card__list">
                    <li>Раскрой по карте проекта без отклонений</li>
                    <li>Пазы, присадки и отверстия — сразу</li>
                    <li>Повтор заказа 1:1 через год</li>
                </ul>
            </article>

            <article class="production-card" data-aos="fade-up" data-aos-delay="100">
                <div class="production-card__num">02</div>
                <div class="production-card__icon">
                    <i class="bi bi-layers"></i>
                </div>
                <h3 class="production-card__title">Кромка на автомате</h3>
                <p class="production-card__text">
                    Кромкооблицовка выполняется на профессиональном автоматическом
                    кромкооблицовочном станке: клей наносится равномерно, кромка прикатывается
                    роликами под давлением и сразу снимается фаска. Итог — монолитная поверхность
                    без зазоров, которая не отходит со временем.
                </p>
                <ul class="production-card__list">
                    <li>Кромка по всему периметру без стыков</li>
                    <li>Влаго- и термостойкий клей</li>
                    <li>Обработка радиусов и криволинейных деталей</li>
                </ul>
            </article>

            <article class="production-card" data-aos="fade-up" data-aos-delay="200">
                <div class="production-card__num">03</div>
                <div class="production-card__icon">
                    <i class="bi bi-gear-wide-connected"></i>
                </div>
                <h3 class="production-card__title">Присадка и фурнитура</h3>
                <p class="production-card__text">
                    Отверстия под петли, направляющие и подъёмные механизмы сверлятся на
                    присадочном станке по единой координатной сетке. Фурнитура Blum, Hettich
                    и Gola встаёт идеально — без подгонки «по месту».
                </p>
                <ul class="production-card__list">
                    <li>Присадка по координатной сетке</li>
                    <li>Фурнитура Blum / Hettich / Gola</li>
                    <li>Плавное закрывание и точная регулировка</li>
                </ul>
            </article>

            <article class="production-card" data-aos="fade-up" data-aos-delay="300">
                <div class="production-card__num">04</div>
                <div class="production-card__icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="production-card__title">Контроль качества</h3>
                <p class="production-card__text">
                    Каждая деталь проходит проверку на трёх этапах: после раскроя, после
                    кромкования и перед сборкой. Мы сверяем геометрию, качество кромки и
                    комплектность — только после этого деталь едет на монтаж.
                </p>
                <ul class="production-card__list">
                    <li>Проверка геометрии на каждом этапе</li>
                    <li>Отсутствие сколов и зазоров</li>
                    <li>Упаковка и доставка в защитной плёнке</li>
                </ul>
            </article>

        </div>

        <div class="production-note" data-aos="fade-up" data-aos-delay="150">
            <i class="bi bi-info-circle"></i>
            <span>Каждый заказ можно приехать и посмотреть на производстве — покажем станки, детали и материалы вживую.</span>
        </div>
    </div>
</section>

<!-- ================= INTERIORS ================= -->
<section class="section section-light feature" id="interiors" data-aos="fade-up">
    <div class="container">
        <div class="feature-grid">
            <div data-aos="fade-right">
                <span class="kicker">03 / Detail</span>
                <h2 class="h-display">Рамочные фасады.<br><em>Тонкая работа с пропорциями.</em></h2>
                <p>Точные зазоры, выразительный профиль и спокойная матовая поверхность. Деталь, которая меняет восприятие всей кухни.</p>

                <div class="detail-tags">
                    <span>01 / ПРОФИЛЬ</span>
                    <span>02 / ЦВЕТ</span>
                    <span>03 / СТЫК</span>
                </div>
            </div>

            <div class="feature-image" data-aos="fade-left">
                <img src="assets/img/projects.jpg" alt="Рамочные фасады">
            </div>
        </div>
    </div>
</section>

<!-- ================= GOLA ================= -->
<section class="feature feature-reverse section section-dark" data-aos="fade-up">
    <div class="container">
        <div class="feature-grid">
            <div class="feature-image" data-aos="fade-right">
                <img src="assets/img/materials.jpg" alt="GOLA профиль">
            </div>
            <div data-aos="fade-left" data-aos-delay="100">
                <span class="kicker">04 / Gola</span>
                <h2 class="h-display">Чистая линия.<br><em>Никаких лишних ручек.</em></h2>
                <p>Профильная система открывания интегрируется в архитектуру фасада и оставляет поверхность визуально цельной.</p>
                <a href="#contact" class="btn btn--outline">
                    Получить консультацию
                    <i class="bi bi-arrow-up-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ================= PROCESS ================= -->
<section class="section section-light process" id="process" data-aos="fade-up">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">05 / Process</span>
                <h2 class="h-display">От идеи<br><em>до готового интерьера</em></h2>
            </div>
            <p class="section-head__text">Один проект. Одна команда. Один ответственный за результат.</p>
        </div>

        <div class="process-grid">
            <article data-aos="fade-up"><span>01</span><h3>Замер</h3><p>Выезд специалиста, точные замеры, анализ помещения.</p></article>
            <article data-aos="fade-up" data-aos-delay="100"><span>02</span><h3>Проектирование</h3><p>Планировка, материалы, 3D-визуализация и согласование.</p></article>
            <article data-aos="fade-up" data-aos-delay="200"><span>03</span><h3>Изготовление</h3><p>Современное производство и контроль каждого узла.</p></article>
            <article data-aos="fade-up" data-aos-delay="300"><span>04</span><h3>Монтаж</h3><p>Доставка, профессиональная установка и финальная проверка.</p></article>
        </div>
    </div>
</section>

<!-- ================= VIDEO ================= -->
<section class="section section-light video-section" data-aos="fade-up">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">06 / Moving image</span>
                <h2 class="h-display">Проекты<br><em>в движении</em></h2>
            </div>
            <p class="section-head__text">Кадры процесса, производства и готовых интерьеров. Нажмите на плитку, чтобы открыть видео.</p>
        </div>

        <div class="video-grid">
            <button class="video-tile"
                    data-video="https://www.youtube.com/watch?v=dQw4w9WgXcQ"
                    data-title="Процесс производства">
                <img src="assets/img/video-1.jpg" alt="Процесс производства" loading="lazy">
                <span class="video-tile__label">01 — PROCESS</span>
                <span class="video-tile__title">Как рождается мебель</span>
                <span class="video-tile__play"><i class="bi bi-play-fill"></i></span>
            </button>

            <button class="video-tile"
                    data-video="https://www.youtube.com/watch?v=dQw4w9WgXcQ"
                    data-title="Готовый проект">
                <img src="assets/img/video-2.jpg" alt="Готовый проект" loading="lazy">
                <span class="video-tile__label">02 — PROJECT</span>
                <span class="video-tile__title">Кухня-гостиная под ключ</span>
                <span class="video-tile__play"><i class="bi bi-play-fill"></i></span>
            </button>

            <button class="video-tile"
                    data-video="https://www.youtube.com/watch?v=dQw4w9WgXcQ"
                    data-title="Детали и материалы">
                <img src="assets/img/video-3.jpg" alt="Детали и материалы" loading="lazy">
                <span class="video-tile__label">03 — DETAIL</span>
                <span class="video-tile__title">Тонкая работа с деталями</span>
                <span class="video-tile__play"><i class="bi bi-play-fill"></i></span>
            </button>
        </div>
    </div>
</section>

<!-- ================= PARTNERS ================= -->
<section class="partners" data-aos="fade-up">
    <div class="container">
        <div class="partners-intro">
            <span class="kicker">06 / Material partners</span>
            <h2 class="h-display">Работаем<br><em>с лучшими</em></h2>
            <p>Материалы и фурнитура, которые выдерживают время и высокие требования к деталям.</p>
        </div>

        <div class="owl-carousel owl-theme partners-carousel">
            <?php foreach ($partners as $p): ?>
            <div class="item partner-slide">
                <?php if (!empty($p['logo_url'])): ?>
                    <img src="<?= h($p['logo_url']) ?>" alt="<?= h($p['name']) ?>" class="partner-logo">
                <?php else: ?>
                    <span class="partner-name"><?= h($p['name'] ?? '') ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ================= CONFIGURATOR / QUIZ ================= -->
<?php /* Quiz modal is rendered after </main> in footer.php to avoid transformed stacking contexts. */ ?>

<!-- ================= CONFIGURATOR ================= -->
<section class="section section-light" id="configurator" data-aos="fade-up">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">07 / Quick estimate</span>
                <h2 class="h-display">Соберём<br><em>первичный запрос</em></h2>
            </div>
            <p class="section-head__text">Это не финальная смета. Мы используем ответы, чтобы быстрее понять задачу.</p>
        </div>

        <form id="configForm" class="config-grid" data-aos="fade-up" data-aos-delay="100">
            <label>Что проектируем
                <select name="type">
                    <option>Кухня</option>
                    <option>Гардеробная</option>
                    <option>Шкаф</option>
                    <option>Комплексный интерьер</option>
                </select>
            </label>

            <label>Площадь / длина
                <input name="size" type="text" placeholder="например, 14 м²">
            </label>

            <label>Стиль
                <select name="style">
                    <option>Тёплый минимализм</option>
                    <option>Современная классика</option>
                    <option>Dark / graphite</option>
                    <option>Не определился</option>
                </select>
            </label>

            <label>Ваш контакт
                <input name="contact" type="text" placeholder="Телефон или Telegram" required>
            </label>

            <button class="btn-solid" type="submit">
                Получить ориентир
                <i class="bi bi-arrow-up-right"></i>
            </button>

            <small class="config-consent">
                Нажимая кнопку, вы соглашаетесь с
                <a href="privacy_policy.php">политикой конфиденциальности</a>
                и обработкой персональных данных.
            </small>
        </form>

        <div id="configResult" class="config-result"></div>
    </div>
</section>

<!-- ================= FAQ ================= -->
<section class="section section-light faq" data-aos="fade-up">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">08 / FAQ</span>
                <h2 class="h-display">Частые<br><em>вопросы</em></h2>
            </div>
        </div>

        <div class="accordion accordion-flush faq-list" id="faqAccordion" data-aos="fade-up" data-aos-delay="100">

            <div class="accordion-item faq-item">
                <h3 class="accordion-header">
                    <button class="accordion-button faq-question collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#faq1"
                            aria-expanded="false"
                            aria-controls="faq1">
                        <span>Можно заказать только мебель без дизайн-проекта?</span>
                        <i class="bi bi-plus faq-icon"></i>
                    </button>
                </h3>
                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body faq-answer">
                        <p>Да. Мы работаем как с отдельными изделиями, так и с комплексными интерьерными задачами.</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item faq-item">
                <h3 class="accordion-header">
                    <button class="accordion-button faq-question collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#faq2"
                            aria-expanded="false"
                            aria-controls="faq2">
                        <span>Вы делаете замер?</span>
                        <i class="bi bi-plus faq-icon"></i>
                    </button>
                </h3>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body faq-answer">
                        <p>Да, замер помещения входит в рабочий процесс и позволяет проектировать мебель с учётом реальной геометрии.</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item faq-item">
                <h3 class="accordion-header">
                    <button class="accordion-button faq-question collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#faq3"
                            aria-expanded="false"
                            aria-controls="faq3">
                        <span>Можно выбрать свои материалы?</span>
                        <i class="bi bi-plus faq-icon"></i>
                    </button>
                </h3>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body faq-answer">
                        <p>Да. Мы можем работать с вашими предпочтениями и предложить альтернативы по фактуре, цене и срокам.</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item faq-item">
                <h3 class="accordion-header">
                    <button class="accordion-button faq-question collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#faq4"
                            aria-expanded="false"
                            aria-controls="faq4">
                        <span>Сколько занимает производство?</span>
                        <i class="bi bi-plus faq-icon"></i>
                    </button>
                </h3>
                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body faq-answer">
                        <p>Срок зависит от состава проекта и материалов. Точный срок фиксируем после утверждения проекта.</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item faq-item">
                <h3 class="accordion-header">
                    <button class="accordion-button faq-question collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#faq5"
                            aria-expanded="false"
                            aria-controls="faq5">
                        <span>Есть ли гарантия на изделия?</span>
                        <i class="bi bi-plus faq-icon"></i>
                    </button>
                </h3>
                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body faq-answer">
                        <p>Да, мы даём письменную гарантию на мебель, фурнитуру и монтаж. Все условия фиксируются в договоре.</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item faq-item">
                <h3 class="accordion-header">
                    <button class="accordion-button faq-question collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#faq6"
                            aria-expanded="false"
                            aria-controls="faq6">
                        <span>Работаете только по Кемерово?</span>
                        <i class="bi bi-plus faq-icon"></i>
                    </button>
                </h3>
                <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body faq-answer">
                        <p>Основной регион — Кемерово и область. Для крупных проектов рассматриваем выезд в другие города — уточняйте у менеджера.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ================= CTA ================= -->
<section class="cta" id="contact" data-aos="fade-up">
    <div class="container">
        <div class="cta__grid">
            <div>
                <span class="kicker">09 / Start a project</span>
                <h2 class="cta__title">Есть пространство.<br><em>Давайте создадим<br>ему характер.</em></h2>
                <p class="cta__desc">Расскажите, что вы хотите получить. Мы предложим концепцию, ориентир по бюджету и следующий шаг.</p>
            </div>

            <form class="cta__form" data-ajax-form>
                <div class="form-field">
                    <input type="text" name="name" id="name" placeholder=" " required>
                    <label for="name">Ваше имя</label>
                </div>
                <div class="form-field">
                    <input type="tel" name="phone" class="phone-mask" id="phone" placeholder=" " required>
                    <label for="phone">Телефон</label>
                </div>
                <button type="submit" class="btn-submit">
                    <span>Отправить заявку</span>
                    <i class="bi bi-arrow-up-right"></i>
                </button>

                <small class="form-consent">
                    Нажимая кнопку, вы соглашаетесь с
                    <a href="privacy_policy.php">политикой конфиденциальности</a>
                    и обработкой персональных данных.
                </small>
            </form>
        </div>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>