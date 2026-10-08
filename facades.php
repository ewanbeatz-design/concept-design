<?php
require_once __DIR__ . '/includes/config.php';

$collection_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$collection_id) {
    header('Location: index.php');
    exit;
}

// ---------- Коллекция ----------
$collection = null;
if ($pdo) {
    $collection = dbRow($pdo,
        "SELECT * FROM facade_collections WHERE id = ? AND is_active = 1",
        [$collection_id]
    );
}

if (!$collection) {
    header('Location: index.php');
    exit;
}

// ---------- Фасады коллекции ----------
$items = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM facade_items
            WHERE collection_id = ?
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$collection_id]);
        $items = $stmt->fetchAll();
    } catch (Throwable $e) {
        $items = [];
    }
}

// ---------- Другие коллекции ----------
$otherCollections = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM facade_collections
            WHERE is_active = 1 AND id != ?
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$collection_id]);
        $otherCollections = $stmt->fetchAll();
    } catch (Throwable $e) {
        $otherCollections = [];
    }
}

// ---------- SEO ----------
$pageTitle       = trim(($collection['title'] ?? '') . ' ' . ($collection['subtitle'] ?? '')) . ' — CONCEPT Design';
$pageDescription = 'Коллекция фасадов ' . ($collection['title'] ?? '') . ' ' . ($collection['subtitle'] ?? '') . ' от студии CONCEPT Design. ' . count($items) . ' вариантов в каталоге.';

include __DIR__ . '/header.php';
?>

<!-- ================= FACADE HERO ================= -->
<section class="facade-hero" data-aos="fade-in">
    <div class="container">
        <a href="index.php#facades" class="facade-hero__back">
            <i class="bi bi-arrow-left"></i> Все коллекции
        </a>

        <div class="facade-hero__content">
            <span class="kicker kicker--light">Коллекция 2026</span>
            <h1 class="facade-hero__title"><?= h($collection['title']) ?></h1>
            <?php if (!empty($collection['subtitle'])): ?>
                <p class="facade-hero__subtitle"><?= h($collection['subtitle']) ?></p>
            <?php endif; ?>
            <p class="facade-hero__count">
                <?= count($items) ?> <?= count($items) === 1 ? 'фасад' : (count($items) < 5 ? 'фасада' : 'фасадов') ?> в коллекции
            </p>
        </div>
    </div>
</section>

<!-- ================= FACADE GALLERY ================= -->
<section class="section section-light">
    <div class="container">
        <?php if (!empty($items)): ?>
            <div class="facade-gallery">
                <?php foreach ($items as $i => $item): ?>
                    <a href="<?= h($item['image_url']) ?>"
                       data-fancybox="facade-<?= (int)$collection_id ?>"
                       data-caption="<?= h($item['title'] ?? '') ?>"
                       class="facade-tile"
                       data-aos="fade-up"
                       data-aos-delay="<?= ($i % 6) * 50 ?>">
                        <div class="facade-tile__image">
                            <img src="<?= h($item['image_url']) ?>"
                                 alt="<?= h($item['title'] ?? $collection['title']) ?>"
                                 loading="lazy">
                            <div class="facade-tile__overlay"></div>
                        </div>
                        <?php if (!empty($item['title'])): ?>
                            <span class="facade-tile__name"><?= h($item['title']) ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                Фасады этой коллекции появятся скоро.
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ================= OTHER COLLECTIONS ================= -->
<?php if (!empty($otherCollections)): ?>
<section class="section section-dark facades">
    <div class="container">
        <div class="facades-head">
            <div class="facades-line"></div>
            <h2 class="facades-title">Другие коллекции</h2>
        </div>

        <div class="facades-grid">
            <?php foreach ($otherCollections as $i => $col): ?>
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
                             alt="<?= h($col['title']) ?>"
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

<!-- ================= CTA ================= -->
<section class="cta" id="contact" data-aos="fade-up">
    <div class="container">
        <div class="cta__grid">
            <div>
                <span class="kicker">Хотите такую кухню?</span>
                <h2 class="cta__title">Обсудим<br><em>ваш проект</em></h2>
                <p class="cta__desc">Оставьте заявку — подберём фасады под ваш интерьер.</p>
            </div>

            <form class="cta__form" data-ajax-form>
                <input type="hidden" name="source" value="Коллекция фасадов: <?= h($collection['title']) ?>">

                <div class="form-field">
                    <input type="text" name="name" id="fc-name" placeholder=" " required>
                    <label for="fc-name">Ваше имя</label>
                </div>
                <div class="form-field">
                    <input type="tel" name="phone" class="phone-mask" id="fc-phone" placeholder=" " required>
                    <label for="fc-phone">Телефон</label>
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