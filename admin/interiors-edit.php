<?php
$pageTitle = 'Интерьер';
require_once __DIR__ . '/includes/header.php';

$id    = (int)($_GET['id'] ?? 0);
$isNew = ($id === 0);

$item = [
    'title'       => '',
    'description' => '',
    'type'        => '',
    'area'        => '',
    'style'       => '',
    'image_url'   => '',
    'sort_order'  => 0,
    'is_active'   => 1,
];

if (!$isNew) {
    $found = dbRow($pdo, "SELECT * FROM interiors WHERE id = ?", [$id]);
    if (!$found) redirect('interiors-list.php');
    $item = array_merge($item, $found);
}
?>

<div class="admin-page-head">
    <a href="interiors-list.php" class="admin-back">
        <i class="bi bi-arrow-left"></i> Все интерьеры
    </a>
</div>

<div class="admin-card">
    <h2 class="admin-card-title"><?= $isNew ? 'Новый интерьер' : 'Редактирование' ?></h2>

    <form method="post" enctype="multipart/form-data" class="admin-form" id="interiorMainForm">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="interior.save">
        <input type="hidden" name="id" value="<?= (int)$id ?>">

        <div class="form-row">
            <label>Название *</label>
            <input type="text" name="title" value="<?= h($item['title']) ?>" required>
        </div>

        <div class="form-row">
            <label>Описание</label>
            <textarea name="description" rows="3"><?= h($item['description']) ?></textarea>
        </div>

        <div class="form-row-2">
            <div class="form-row">
                <label>Тип</label>
                <input type="text" name="type" value="<?= h($item['type']) ?>" placeholder="Гостиная, Спальня, Кухня">
            </div>
            <div class="form-row">
                <label>Площадь</label>
                <input type="text" name="area" value="<?= h($item['area']) ?>" placeholder="34 м²">
            </div>
        </div>

        <div class="form-row-2">
            <div class="form-row">
                <label>Стиль</label>
                <input type="text" name="style" value="<?= h($item['style']) ?>" placeholder="Современный">
            </div>
            <div class="form-row">
                <label>Порядок сортировки</label>
                <input type="number" name="sort_order" value="<?= (int)$item['sort_order'] ?>">
            </div>
        </div>

        <div class="form-row">
            <label>Изображение</label>
            <div class="upload-field">
                <input type="file" name="image_file" accept="image/*">
                <?php if (!empty($item['image_url'])): ?>
                    <div class="upload-preview">
                        <img src="../<?= h($item['image_url']) ?>" alt="">
                    </div>
                <?php endif; ?>
            </div>
            <input type="text" name="image_url" value="<?= h($item['image_url']) ?>" placeholder="или путь: assets/uploads/...">
        </div>

        <div class="form-row form-row--inline">
            <label class="checkbox">
                <input type="checkbox" name="is_active" value="1" <?= $item['is_active'] ? 'checked' : '' ?>>
                <span>Активен на сайте</span>
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="admin-btn admin-btn--primary">
                <i class="bi bi-check2"></i> <?= $isNew ? 'Создать' : 'Сохранить' ?>
            </button>
            <a href="interiors-list.php" class="admin-btn admin-btn--outline">Отмена</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>