<?php
require_once __DIR__ . '/includes/config.php';

$pageTitle       = 'Все проекты — CONCEPT Design';
$pageDescription = 'Портфолио студии CONCEPT Design: кухни, гардеробные, шкафы-купе и комплексные интерьеры под ключ в Кемерово.';

// Все активные проекты
$projects = dbRows($pdo,
    "SELECT * FROM projects WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC",
    [
        ['id' => 1, 'title' => 'Гардеробная с подсветкой', 'description' => 'Встроенная гардеробная с системой хранения', 'image_url' => 'assets/img/projects-1.jpg', 'type' => 'FURNITURE', 'style' => 'Минимализм', 'area' => '6 м²'],
        ['id' => 2, 'title' => 'Кухня-гостиная',            'description' => 'Объединённое пространство с кухонным островом', 'image_url' => 'assets/img/projects-2.jpg', 'type' => 'INTERIOR',  'style' => 'Современный', 'area' => '28 м²'],
        ['id' => 3, 'title' => 'Шкаф-купе в спальню',       'description' => 'Шкаф-купе с зеркальными фасадами',            'image_url' => 'assets/img/projects-3.jpg', 'type' => 'FURNITURE', 'style' => 'Классика',    'area' => '4 м²'],
        ['id' => 4, 'title' => 'Квартира с характером',     'description' => 'Тёплый минимализм, натуральный шпон и камень','image_url' => 'assets/img/projects-4.jpg', 'type' => 'INTERIOR',  'style' => 'Минимализм', 'area' => '72 м²'],
        ['id' => 5, 'title' => 'Кухня с островом',          'description' => 'Компактная кухня с барной стойкой',           'image_url' => 'assets/img/projects-5.jpg', 'type' => 'FURNITURE', 'style' => 'Лофт',        'area' => '18 м²'],
        ['id' => 6, 'title' => 'Гостиная под ключ',         'description' => 'Встроенная мебель и свет в одном стиле',      'image_url' => 'assets/img/projects-6.jpg', 'type' => 'INTERIOR',  'style' => 'Современный', 'area' => '34 м²'],
    ]
);

// Уникальные категории для фильтра
$types = [];
foreach ($projects as $p) {
    if (!empty($p['type']) && !in_array($p['type'], $types, true)) {
        $types[] = $p['type'];
    }
}

include __DIR__ . '/header.php';
?>

<!-- ================= HERO ================= -->
<section class="section" style="padding-top: 160px; padding-bottom: 40px;">
    <div class="container">
        <div class="section-head" style="margin-bottom: 40px;">
            <div>
                <span class="kicker">Selected work</span>
                <h1 class="h-display" style="margin: 0;">Все проекты<br><em>студии</em></h1>
            </div>
            <p class="section-head__text">
                <?= count($projects) ?> проектов — от отдельных предметов мебели до комплексных интерьеров под ключ.
            </p>
        </div>

        <?php if (!empty($types)): ?>
        <div class="projects-filter" data-aos="fade-up">
            <button class="filter-btn is-active" data-filter="all">Все</button>
            <?php foreach ($types as $type): ?>
                <button class="filter-btn" data-filter="<?= h($type) ?>"><?= h($type) ?></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ================= PROJECTS GRID ================= -->
<section class="section" style="padding-top: 20px;" data-aos="fade-up">
    <div class="container">
        <div class="projects-grid" id="projectsGrid">
            <?php foreach ($projects as $i => $p): ?>
            <article class="project-tile" data-type="<?= h($p['type'] ?? '') ?>">
                <a href="project.php?id=<?= (int)$p['id'] ?>" class="project-tile__link">
                    <div class="project-tile__image">
                        <img src="<?= h($p['image_url'] ?: 'assets/img/placeholder.jpg') ?>"
                             alt="<?= h($p['title']) ?>"
                             loading="lazy">
                        <span class="project-tile__num">— <?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    </div>

                    <div class="project-tile__info">
                        <div>
                            <span class="project-tile__type"><?= h($p['type'] ?? '') ?></span>
                            <h3 class="project-tile__title"><?= h($p['title']) ?></h3>
                            <?php if (!empty($p['description'])): ?>
                                <p class="project-tile__desc"><?= h($p['description']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="project-tile__meta">
                            <?php if (!empty($p['area'])): ?>
                                <span><?= h($p['area']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($p['style'])): ?>
                                <span><?= h($p['style']) ?></span>
                            <?php endif; ?>
                            <i class="bi bi-arrow-up-right"></i>
                        </div>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>

        <div class="projects-empty" id="projectsEmpty" hidden>
            <p>В этой категории пока нет проектов.</p>
        </div>
    </div>
</section>

<!-- ================= CTA ================= -->
<section class="cta" id="contact" data-aos="fade-up">
    <div class="container">
        <div class="cta__grid">
            <div>
                <span class="kicker">Start a project</span>
                <h2 class="cta__title">Хотите<br><em>свой проект?</em></h2>
                <p class="cta__desc">Расскажите о задаче — предложим концепцию и ориентир по бюджету.</p>
            </div>

            <form class="cta__form" data-ajax-form>
                <input type="hidden" name="source" value="Страница всех проектов">

                <div class="form-field">
                    <input type="text" name="name" id="pp-name" placeholder=" " required>
                    <label for="pp-name">Ваше имя</label>
                </div>
                <div class="form-field">
                    <input type="tel" name="phone" class="phone-mask" id="pp-phone" placeholder=" " required>
                    <label for="pp-phone">Телефон</label>
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