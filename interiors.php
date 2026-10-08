<?php
require_once __DIR__ . '/includes/config.php';

$pageTitle       = 'Дизайн интерьеров — CONCEPT Design';
$pageDescription = 'Дизайн интерьеров под ключ в Кемерово: гостиные, спальни, кухни, детские. Реализованные проекты студии CONCEPT Design.';

// Все активные интерьеры
$interiors = dbRows($pdo,
    "SELECT * FROM interiors WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC"
);

// Уникальные типы для фильтра
$types = [];
foreach ($interiors as $item) {
    if (!empty($item['type']) && !in_array($item['type'], $types, true)) {
        $types[] = $item['type'];
    }
}

include __DIR__ . '/header.php';
?>

<!-- ================= HERO ================= -->
<section class="section" style="padding-top: 160px; padding-bottom: 40px;">
    <div class="container">
        <div class="section-head" style="margin-bottom: 40px;">
            <div>
                <span class="kicker">Interior design</span>
                <h1 class="h-display" style="margin: 0;">Интерьеры<br><em>для гармонии жизни</em></h1>
            </div>
            <p class="section-head__text">
                <?= count($interiors) ?> проектов — жилые и общественные интерьеры, реализованные студией CONCEPT Design.
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

<!-- ================= INTERIORS GRID ================= -->
<section class="section" style="padding-top: 20px;" data-aos="fade-up">
    <div class="container">
        <div class="projects-grid" id="interiorsGrid">
            <?php foreach ($interiors as $i => $item): ?>
            <article class="project-tile" data-type="<?= h($item['type'] ?? '') ?>">
                <a href="<?= !empty($item['slug']) ? 'interior/' . h($item['slug']) : 'interior.php?id=' . (int)$item['id'] ?>"
                   class="project-tile__link">

                    <div class="project-tile__image">
                        <img src="<?= h($item['image_url'] ?: 'assets/img/placeholder.jpg') ?>"
                             alt="<?= h($item['title']) ?>"
                             loading="lazy">
                        <span class="project-tile__num">— <?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    </div>

                    <div class="project-tile__info">
                        <div>
                            <span class="project-tile__type"><?= h($item['type'] ?? '') ?></span>
                            <h3 class="project-tile__title"><?= h($item['title']) ?></h3>
                            <?php if (!empty($item['description'])): ?>
                                <p class="project-tile__desc"><?= h($item['description']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="project-tile__meta">
                            <?php if (!empty($item['area'])): ?>
                                <span><?= h($item['area']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($item['style'])): ?>
                                <span><?= h($item['style']) ?></span>
                            <?php endif; ?>
                            <i class="bi bi-arrow-up-right"></i>
                        </div>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>

        <div class="projects-empty" id="interiorsEmpty" hidden>
            <p>В этой категории пока нет интерьеров.</p>
        </div>
    </div>
</section>

<!-- ================= CTA ================= -->
<section class="cta" id="contact" data-aos="fade-up">
    <div class="container">
        <div class="cta__grid">
            <div>
                <span class="kicker">Start a project</span>
                <h2 class="cta__title">Хотите<br><em>свой интерьер?</em></h2>
                <p class="cta__desc">Расскажите о задаче — предложим концепцию и ориентир по бюджету.</p>
            </div>

            <form class="cta__form" data-ajax-form>
                <input type="hidden" name="source" value="Страница всех интерьеров">

                <div class="form-field">
                    <input type="text" name="name" id="int-name" placeholder=" " required>
                    <label for="int-name">Ваше имя</label>
                </div>
                <div class="form-field">
                    <input type="tel" name="phone" class="phone-mask" id="int-phone" placeholder=" " required>
                    <label for="int-phone">Телефон</label>
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