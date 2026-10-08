<?php
require_once __DIR__ . '/includes/config.php';

$interior_id   = isset($_GET['id'])   ? (int)$_GET['id'] : 0;
$interior_slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';

$interior = null;

if ($pdo) {
    if ($interior_slug !== '') {
        $interior = dbRow($pdo,
            "SELECT * FROM interiors WHERE slug = ? AND is_active = 1 LIMIT 1",
            [$interior_slug]
        );
    } elseif ($interior_id > 0) {
        $interior = dbRow($pdo,
            "SELECT * FROM interiors WHERE id = ? AND is_active = 1 LIMIT 1",
            [$interior_id]
        );
    }
}

if (!$interior) {
    header('Location: interiors.php');
    exit;
}

// Если пришли по ?id= и у интерьера есть slug — редирект на красивый URL
if ($interior_slug === '' && !empty($interior['slug'])) {
    header('Location: /interior/' . $interior['slug'], true, 301);
    exit;
}

$pageTitle       = h($interior['title']) . ' — CONCEPT Design';
$pageDescription = mb_substr(strip_tags((string)($interior['description'] ?? '')), 0, 160);

// Другие интерьеры
$otherInteriors = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM interiors
            WHERE is_active = 1 AND id != ?
            ORDER BY RAND()
            LIMIT 3
        ");
        $stmt->execute([$interior['id']]);
        $otherInteriors = $stmt->fetchAll();
    } catch (Throwable $e) {}
}

include __DIR__ . '/header.php';
?>

<!-- ================= INTERIOR HERO ================= -->
<section class="interior-hero" data-aos="fade-in">
    <div class="container">
        <a href="interiors.php" class="interior-hero__back">
            <i class="bi bi-arrow-left"></i> Все интерьеры
        </a>

        <div class="interior-hero__content">
            <span class="kicker kicker--light"><?= h($interior['type'] ?? 'Интерьер') ?></span>
            <h1 class="interior-hero__title"><?= h($interior['title']) ?></h1>

            <?php if (!empty($interior['description'])): ?>
                <p class="interior-hero__desc"><?= h($interior['description']) ?></p>
            <?php endif; ?>

            <div class="interior-hero__meta">
                <?php if (!empty($interior['area'])): ?>
                    <div class="interior-hero__stat">
                        <span>Площадь</span>
                        <strong><?= h($interior['area']) ?></strong>
                    </div>
                <?php endif; ?>

                <?php if (!empty($interior['style'])): ?>
                    <div class="interior-hero__stat">
                        <span>Стиль</span>
                        <strong><?= h($interior['style']) ?></strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ================= INTERIOR IMAGE ================= -->
<?php if (!empty($interior['image_url'])): ?>
<section class="section section-light">
    <div class="container">
        <div class="interior-single__image">
            <img src="<?= h($interior['image_url']) ?>"
                 alt="<?= h($interior['title']) ?>">
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ================= OTHER INTERIORS ================= -->
<?php if (!empty($otherInteriors)): ?>
<section class="section section-light interior-gallery">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">Другие интерьеры</span>
                <h2 class="h-display">Смотрите<br><em>также</em></h2>
            </div>
            <a href="/concept-2026/interiors.php" class="btn btn--outline btn--sm">
                Все интерьеры
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="interiors-grid">
            <?php foreach ($otherInteriors as $i => $item): ?>
            <a href="<?= !empty($item['slug']) ? '/interior/' . h($item['slug']) : 'interior.php?id=' . (int)$item['id'] ?>"
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

<!-- ================= CTA ================= -->
<section class="cta" id="contact">
    <div class="container">
        <div class="cta__grid">
            <div>
                <span class="kicker">Хотите так же?</span>
                <h2 class="cta__title">Обсудим<br><em>ваш интерьер</em></h2>
                <p class="cta__desc">Оставьте заявку — предложим концепцию и ориентир по бюджету.</p>
            </div>

            <form class="cta__form" data-ajax-form>
                <input type="hidden" name="source" value="Интерьер: <?= h($interior['title']) ?>">

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