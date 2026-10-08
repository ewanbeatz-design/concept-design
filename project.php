<?php
require_once __DIR__ . '/includes/config.php';

$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ---------- Проект ----------
$project = null;

if ($pdo && $project_id) {
    $project = dbRow($pdo, "SELECT * FROM projects WHERE id = ? AND is_active = 1", [$project_id]);
    if ($project) {
        try {
            $pdo->prepare("UPDATE projects SET views = views + 1 WHERE id = ?")->execute([$project_id]);
        } catch (Throwable $e) {}
        $project = dbRow($pdo, "SELECT * FROM projects WHERE id = ?", [$project_id]);
    }
}

if (!$project) {
    header('Location: index.php');
    exit;
}

// ---------- SEO ----------
$pageTitle       = h($project['title']) . ' — CONCEPT Design';
$pageDescription = mb_substr(strip_tags((string)($project['description'] ?? '')), 0, 160);

// ---------- Связанные данные ----------
$project_furniture = [];
$project_materials = [];
$project_features  = [];
$related_projects  = [];

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM project_furniture WHERE project_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$project_id]);
        $project_furniture = $stmt->fetchAll();
    } catch (Throwable $e) {}

    try {
        $stmt = $pdo->prepare("SELECT * FROM project_materials WHERE project_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$project_id]);
        $project_materials = $stmt->fetchAll();
    } catch (Throwable $e) {}

    try {
        $stmt = $pdo->prepare("SELECT * FROM project_features WHERE project_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$project_id]);
        $project_features = $stmt->fetchAll();
    } catch (Throwable $e) {}

    try {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE is_active = 1 AND id != ? ORDER BY RAND() LIMIT 4");
        $stmt->execute([$project_id]);
        $related_projects = $stmt->fetchAll();
    } catch (Throwable $e) {}
}

// ---------- Галерея ----------
$gallery = json_decode($project['gallery_images'] ?? '[]', true);
$gallery = array_values(array_filter(is_array($gallery) ? $gallery : []));
if (empty($gallery)) {
    $gallery = array_values(array_filter([$project['image_url'] ?? '']));
}

// ---------- Лайки / просмотры ----------
$views    = (int)($project['views'] ?? 0);
$likes    = (int)($project['likes'] ?? 0);
$user_ip  = $_SERVER['REMOTE_ADDR'] ?? '';
$likeKey  = 'project_like_' . $project_id . '_' . md5($user_ip);
$hasLiked = !empty($_SESSION[$likeKey]);

include __DIR__ . '/header.php';
?>

<!-- ================= PROJECT HERO ================= -->
<section class="project-hero" data-aos="fade-in">
    <div class="project-hero__media">
        <img src="<?= h($project['image_url'] ?: 'assets/img/placeholder.jpg') ?>"
             alt="<?= h($project['title']) ?>">
        <div class="project-hero__overlay"></div>
    </div>

    <div class="container project-hero__inner">
        <div class="project-hero__top">
            <span class="kicker kicker--light">Проект</span>
            <a href="projects.php" class="project-hero__back">
                <i class="bi bi-arrow-left"></i> Все проекты
            </a>
        </div>

        <div class="project-hero__grid">
            <div class="project-hero__copy">
                <h1 class="project-hero__title"><?= h($project['title']) ?></h1>
                <p class="project-hero__desc"><?= h($project['description']) ?></p>

                <div class="project-hero__meta">
                    <?php if (!empty($project['type'])): ?>
                    <div class="project-hero__stat">
                        <span>Тип</span>
                        <strong><?= h($project['type']) ?></strong>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($project['area'])): ?>
                    <div class="project-hero__stat">
                        <span>Площадь</span>
                        <strong><?= h($project['area']) ?></strong>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($project['style'])): ?>
                    <div class="project-hero__stat">
                        <span>Стиль</span>
                        <strong><?= h($project['style']) ?></strong>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($project['duration'])): ?>
                    <div class="project-hero__stat">
                        <span>Срок</span>
                        <strong><?= h($project['duration']) ?></strong>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="project-hero__actions">
                    <button type="button"
                            class="hero__like <?= $hasLiked ? 'liked' : '' ?>"
                            id="likeBtn"
                            data-project-id="<?= $project_id ?>"
                            data-liked="<?= $hasLiked ? 'true' : 'false' ?>">
                        <i class="bi bi-heart<?= $hasLiked ? '-fill' : '' ?>"></i>
                        <span id="likesCount"><?= number_format($likes, 0, '', ' ') ?> лайков</span>
                    </button>

                    <span class="project-hero__views">
                        <i class="bi bi-eye"></i>
                        <?= number_format($views, 0, '', ' ') ?>
                    </span>
                </div>
            </div>

            <?php if (!empty($project_materials) || !empty($project_features)): ?>
            <aside class="project-hero__used">
                <?php if (!empty($project_materials)): ?>
                <h2 class="used-card__title">Материалы</h2>

                <div class="used-card__materials">
                    <?php foreach ($project_materials as $m): ?>
                    <div class="used-material">
                        <div class="used-material__swatch"
                             style="background: <?= h($m['color_code'] ?: '#e8e5de') ?>;"></div>
                        <div class="used-material__name"><?= h($m['name']) ?></div>
                        <?php if (!empty($m['brand'])): ?>
                            <div class="used-material__brand"><?= h($m['brand']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($project_features)): ?>
                <div class="used-card__divider"></div>

                <h2 class="used-card__title">Особенности</h2>

                <ul class="used-card__features">
                    <?php foreach ($project_features as $f): ?>
                    <li>
                        <i class="bi bi-check-circle-fill"></i>
                        <span><?= h($f['feature']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </aside>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ================= GALLERY ================= -->
<?php if (!empty($gallery)): ?>
<section class="section section-light" id="gallery">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">— 01 / Галерея</span>
                <h2 class="h-display">Фотографии<br><em>проекта</em></h2>
            </div>
            <p class="section-head__text">
                <?= count($gallery) ?> изображений. Нажмите на фото, чтобы открыть в полном размере.
            </p>
        </div>

        <div class="owl-carousel owl-theme project-gallery-carousel">
            <?php foreach ($gallery as $i => $img): ?>
            <div class="item">
                <a href="<?= h($img) ?>" data-fancybox="gallery" class="project-gallery__item">
                    <div class="project-gallery__image">
                        <img src="<?= h($img) ?>" alt="<?= h($project['title']) ?>" loading="lazy">
                    </div>
                    <div class="project-gallery__overlay">
                        <span>— <?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?> / <?= str_pad((string)count($gallery), 2, '0', STR_PAD_LEFT) ?></span>
                        <i class="bi bi-arrow-up-right"></i>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ================= TASK / SOLUTION ================= -->
<?php if (!empty($project['challenge']) || !empty($project['solution'])): ?>
<section class="section section--dark">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">— 02 / Задача и решение</span>
                <h2 class="h-display">Что стояло<br><em>перед нами</em></h2>
            </div>
        </div>

        <div class="task-solution">
            <?php if (!empty($project['challenge'])): ?>
            <div class="ts-block">
                <div class="ts-block__label"><span></span>Задача</div>
                <h3 class="ts-block__title">С чем мы столкнулись</h3>
                <p class="ts-block__text"><?= h($project['challenge']) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($project['solution'])): ?>
            <div class="ts-block">
                <div class="ts-block__label"><span></span>Решение</div>
                <h3 class="ts-block__title">Как мы это решили</h3>
                <p class="ts-block__text"><?= h($project['solution']) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ================= BEFORE / AFTER ================= -->
<?php if (!empty($project['before_image']) || !empty($project['after_image'])): ?>
<section class="section section--dark">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">— 03 / Трансформация</span>
                <h2 class="h-display">Было<br><em>и стало</em></h2>
            </div>
        </div>

        <div class="ba-grid">
            <?php if (!empty($project['before_image'])): ?>
            <div class="ba-card">
                <span class="ba-label ba-label--before">До</span>
                <img src="<?= h($project['before_image']) ?>" alt="До">
            </div>
            <?php endif; ?>

            <?php if (!empty($project['after_image'])): ?>
            <div class="ba-card">
                <span class="ba-label ba-label--after">После</span>
                <img src="<?= h($project['after_image']) ?>" alt="После">
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ================= FURNITURE ================= -->
<?php if (!empty($project_furniture)): ?>
<section class="section section-light">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">— 04 / Мебель проекта</span>
                <h2 class="h-display">Что было<br><em>изготовлено</em></h2>
            </div>
        </div>

        <div class="furniture-rows">
            <?php foreach ($project_furniture as $i => $item): ?>
            <a href="<?= h($item['link'] ?: '#cta') ?>" class="furniture-row">
                <span class="furniture-row__num">
                    — <?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?>
                </span>

                <div class="furniture-row__img">
                    <img src="<?= h($item['image_url']) ?>" alt="<?= h($item['name']) ?>" loading="lazy">
                </div>

                <div>
                    <h3 class="furniture-row__title"><?= h($item['name']) ?></h3>
                    <?php if (!empty($item['description'])): ?>
                        <p class="furniture-row__desc"><?= h($item['description']) ?></p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($item['price'])): ?>
                    <div class="furniture-row__price"><?= h($item['price']) ?></div>
                <?php endif; ?>

                <div class="furniture-row__arrow">
                    <i class="bi bi-arrow-up-right"></i>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ================= RELATED ================= -->
<?php if (!empty($related_projects)): ?>
<section class="section section-light">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">— 05 / Ещё проекты</span>
                <h2 class="h-display">Смотрите<br><em>также</em></h2>
            </div>
            <a href="projects.php" class="btn btn--outline btn--sm">
                Все проекты
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="related-grid">
            <?php foreach (array_slice($related_projects, 0, 4) as $related): ?>
            <a href="project.php?id=<?= (int)$related['id'] ?>" class="related-card">
                <div class="related-card__img">
                    <img src="<?= h($related['image_url']) ?>"
                         alt="<?= h($related['title']) ?>"
                         loading="lazy">
                </div>
                <div class="related-card__info">
                    <div>
                        <h3 class="related-card__title"><?= h($related['title']) ?></h3>
                        <?php if (!empty($related['type'])): ?>
                            <div class="related-card__cat"><?= h($related['type']) ?></div>
                        <?php endif; ?>
                    </div>
                    <span class="related-card__arrow"><i class="bi bi-arrow-up-right"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ================= CTA ================= -->
<section class="cta" id="cta">
    <div class="container">
        <div class="cta__grid">
            <div>
                <span class="kicker">— 06 / Начать проект</span>
                <h2 class="cta__title">Обсудим<br><em>ваш проект</em></h2>
                <p class="cta__desc">Оставьте заявку — мы свяжемся с вами в ближайшее время.</p>
            </div>

            <form class="cta__form" data-ajax-form>
                <input type="hidden" name="project_id" value="<?= $project_id ?>">
                <input type="hidden" name="project_title" value="<?= h($project['title']) ?>">
                <input type="hidden" name="source" value="Страница проекта">

                <div class="form-field">
                    <input type="text" name="name" id="proj-name" placeholder=" " required>
                    <label for="proj-name">Ваше имя</label>
                </div>
                <div class="form-field">
                    <input type="tel" name="phone" class="phone-mask" id="proj-phone" placeholder=" " required>
                    <label for="proj-phone">Телефон</label>
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