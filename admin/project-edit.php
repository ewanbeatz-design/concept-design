<?php
$pageTitle = 'Проект';
require_once __DIR__ . '/includes/header.php';

$id     = (int)($_GET['id'] ?? 0);
$isNew  = ($id === 0);
$tab    = $_GET['tab'] ?? 'main';

$project = [
    'title'          => '',
    'description'    => '',
    'area'           => '',
    'duration'       => '',
    'style'          => '',
    'type'           => '',
    'challenge'      => '',
    'solution'       => '',
    'before_image'   => '',
    'after_image'    => '',
    'gallery_images' => '',
    'category'       => '',
    'image_url'      => '',
    'thumbnail_url'  => '',
    'sort_order'     => 0,
    'is_active'      => 1,
];

$materials = [];
$furniture = [];
$features  = [];

// Загрузка
if (!$isNew) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) redirect('projects.php');
    $project = array_merge($project, $found);

    $stmt = $pdo->prepare("SELECT * FROM project_materials WHERE project_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$id]);
    $materials = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM project_furniture WHERE project_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$id]);
    $furniture = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM project_features WHERE project_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$id]);
    $features = $stmt->fetchAll();
}

$success = '';

// СОХРАНЕНИЕ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? 'save_main';

    // 1. Основные данные
    if ($action === 'save_main') {
        $data = [
            'title'          => trim($_POST['title'] ?? ''),
            'description'    => trim($_POST['description'] ?? ''),
            'area'           => trim($_POST['area'] ?? ''),
            'duration'       => trim($_POST['duration'] ?? ''),
            'style'          => trim($_POST['style'] ?? ''),
            'type'           => trim($_POST['type'] ?? ''),
            'challenge'      => trim($_POST['challenge'] ?? ''),
            'solution'       => trim($_POST['solution'] ?? ''),
            'category'       => trim($_POST['category'] ?? ''),
            'sort_order'     => (int)($_POST['sort_order'] ?? 0),
            'is_active'      => isset($_POST['is_active']) ? 1 : 0,
        ];

        // Превью
        if (!empty($_FILES['image_file']['tmp_name'])) {
            $url = upload_image($_FILES['image_file'], 'projects');
            if ($url) $data['image_url'] = $url;
        } elseif (!empty($_POST['image_url'])) {
            $data['image_url'] = trim($_POST['image_url']);
        }

        // Before / After
        if (!empty($_FILES['before_file']['tmp_name'])) {
            $url = upload_image($_FILES['before_file'], 'projects/before');
            if ($url) $data['before_image'] = $url;
        }
        if (!empty($_FILES['after_file']['tmp_name'])) {
            $url = upload_image($_FILES['after_file'], 'projects/after');
            if ($url) $data['after_image'] = $url;
        }

        if ($isNew) {
            $newId = db_insert($pdo, 'projects', $data);
            redirect('project-edit.php?id=' . $newId . '&tab=main');
        } else {
            db_update($pdo, 'projects', $data, 'id = :id', ['id' => $id]);
            $success = 'Сохранено';
        }

        // Перезагрузка
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $project = array_merge($project, $stmt->fetch() ?: []);
    }

    // 2. Галерея
    if ($action === 'save_gallery' && !$isNew) {
        $urls = [];
        if (!empty($_POST['gallery_urls'])) {
            foreach (explode("\n", $_POST['gallery_urls']) as $line) {
                $line = trim($line);
                if ($line !== '') $urls[] = $line;
            }
        }
        if (!empty($_FILES['gallery_files']['tmp_name'])) {
            foreach ($_FILES['gallery_files']['tmp_name'] as $i => $tmp) {
                if (($_FILES['gallery_files']['error'][$i] ?? 0) !== UPLOAD_ERR_OK) continue;
                $url = upload_image([
                    'name'     => $_FILES['gallery_files']['name'][$i],
                    'tmp_name' => $tmp,
                    'error'    => $_FILES['gallery_files']['error'][$i],
                    'size'     => $_FILES['gallery_files']['size'][$i],
                ], 'projects/gallery');
                if ($url) $urls[] = $url;
            }
        }
        $galleryJson = json_encode($urls, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        db_update($pdo, 'projects', ['gallery_images' => $galleryJson], 'id = :id', ['id' => $id]);
        $project['gallery_images'] = $galleryJson;
        $success = 'Галерея сохранена';
    }

    // 3. Материалы
    if ($action === 'save_materials' && !$isNew) {
        $pdo->prepare("DELETE FROM project_materials WHERE project_id = ?")->execute([$id]);
        if (!empty($_POST['materials']) && is_array($_POST['materials'])) {
            $i = 0;
            foreach ($_POST['materials'] as $m) {
                if (empty($m['name'])) continue;
                db_insert($pdo, 'project_materials', [
                    'project_id' => $id,
                    'name'       => trim($m['name']),
                    'brand'      => trim($m['brand'] ?? ''),
                    'color_code' => trim($m['color_code'] ?? '#cccccc'),
                    'sort_order' => $i++,
                ]);
            }
        }
        redirect('project-edit.php?id=' . $id . '&tab=materials');
    }

    // 4. Мебель
    if ($action === 'save_furniture' && !$isNew) {
        $pdo->prepare("DELETE FROM project_furniture WHERE project_id = ?")->execute([$id]);
        if (!empty($_POST['furniture']) && is_array($_POST['furniture'])) {
            $i = 0;
            foreach ($_POST['furniture'] as $f) {
                if (empty($f['name'])) continue;
                db_insert($pdo, 'project_furniture', [
                    'project_id'  => $id,
                    'name'        => trim($f['name']),
                    'description' => trim($f['description'] ?? ''),
                    'image_url'   => trim($f['image_url'] ?? ''),
                    'price'       => trim($f['price'] ?? ''),
                    'link'        => trim($f['link'] ?? ''),
                    'sort_order'  => $i++,
                ]);
            }
        }
        redirect('project-edit.php?id=' . $id . '&tab=furniture');
    }

    // 5. Особенности
    if ($action === 'save_features' && !$isNew) {
        $pdo->prepare("DELETE FROM project_features WHERE project_id = ?")->execute([$id]);
        if (!empty($_POST['features']) && is_array($_POST['features'])) {
            $i = 0;
            foreach ($_POST['features'] as $f) {
                if (empty($f['feature'])) continue;
                db_insert($pdo, 'project_features', [
                    'project_id' => $id,
                    'feature'    => trim($f['feature']),
                    'icon'       => trim($f['icon'] ?? 'fa-regular fa-circle-check'),
                    'sort_order' => $i++,
                ]);
            }
        }
        redirect('project-edit.php?id=' . $id . '&tab=features');
    }
}

$gallery = json_decode($project['gallery_images'] ?? '[]', true);
if (!is_array($gallery)) $gallery = [];

$tabs = [
    'main'      => 'Основное',
    'gallery'   => 'Галерея',
    'materials' => 'Материалы',
    'furniture' => 'Мебель',
    'features'  => 'Особенности',
];
if ($isNew) $tabs = ['main' => 'Основное'];
?>

<div class="admin-page-head">
    <a href="projects.php" class="admin-back">
        <i class="bi bi-arrow-left"></i> Все проекты
    </a>

    <?php if (!$isNew): ?>
    <a href="../project.php?id=<?= (int)$id ?>" target="_blank" class="admin-btn admin-btn--outline">
        <i class="bi bi-box-arrow-up-right"></i> Открыть на сайте
    </a>
    <?php endif; ?>
</div>

<?php if ($success): ?>
    <div class="admin-alert admin-alert--success">
        <i class="bi bi-check2-circle"></i> <?= h($success) ?>
    </div>
<?php endif; ?>

<!-- TABS -->
<div class="admin-tabs">
    <?php foreach ($tabs as $key => $label): ?>
        <a href="?<?= $isNew ? '' : 'id=' . (int)$id . '&' ?>tab=<?= $key ?>"
           class="admin-tab <?= $tab === $key ? 'active' : '' ?>">
            <?= h($label) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- MAIN -->
<?php if ($tab === 'main'): ?>
<div class="admin-card">
    <h2 class="admin-card-title"><?= $isNew ? 'Новый проект' : 'Редактирование проекта' ?></h2>

    <form method="post" enctype="multipart/form-data" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_main">

        <div class="form-row">
            <label>Название *</label>
            <input type="text" name="title" value="<?= h($project['title']) ?>" required>
        </div>

        <div class="form-row">
            <label>Краткое описание</label>
            <textarea name="description" rows="2"><?= h($project['description']) ?></textarea>
        </div>

        <div class="form-row-2">
            <div class="form-row">
                <label>Площадь</label>
                <input type="text" name="area" value="<?= h($project['area']) ?>" placeholder="60 м²">
            </div>
            <div class="form-row">
                <label>Срок</label>
                <input type="text" name="duration" value="<?= h($project['duration']) ?>" placeholder="3 недели">
            </div>
        </div>

        <div class="form-row-2">
            <div class="form-row">
                <label>Стиль</label>
                <input type="text" name="style" value="<?= h($project['style']) ?>">
            </div>
            <div class="form-row">
                <label>Тип</label>
                <input type="text" name="type" value="<?= h($project['type']) ?>" placeholder="Кухня / Гардеробная">
            </div>
        </div>

        <div class="form-row-2">
            <div class="form-row">
                <label>Категория (slug)</label>
                <input type="text" name="category" value="<?= h($project['category']) ?>" placeholder="kitchen">
            </div>
            <div class="form-row">
                <label>Порядок сортировки</label>
                <input type="number" name="sort_order" value="<?= (int)$project['sort_order'] ?>">
            </div>
        </div>

        <div class="form-row">
            <label>Задача</label>
            <textarea name="challenge" rows="3"><?= h($project['challenge']) ?></textarea>
        </div>

        <div class="form-row">
            <label>Решение</label>
            <textarea name="solution" rows="3"><?= h($project['solution']) ?></textarea>
        </div>

        <div class="form-row">
            <label>Главное изображение</label>
            <div class="upload-field">
                <input type="file" name="image_file" accept="image/*">
                <?php if (!empty($project['image_url'])): ?>
                    <div class="upload-preview">
                        <img src="../<?= h($project['image_url']) ?>" alt="">
                    </div>
                <?php endif; ?>
            </div>
            <input type="text" name="image_url" value="<?= h($project['image_url']) ?>" placeholder="или путь: assets/uploads/...">
        </div>

        <div class="form-row-2">
            <div class="form-row">
                <label>Фото «До»</label>
                <div class="upload-field">
                    <input type="file" name="before_file" accept="image/*">
                    <?php if (!empty($project['before_image'])): ?>
                        <div class="upload-preview">
                            <img src="../<?= h($project['before_image']) ?>" alt="">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-row">
                <label>Фото «После»</label>
                <div class="upload-field">
                    <input type="file" name="after_file" accept="image/*">
                    <?php if (!empty($project['after_image'])): ?>
                        <div class="upload-preview">
                            <img src="../<?= h($project['after_image']) ?>" alt="">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="form-row form-row--inline">
            <label class="checkbox">
                <input type="checkbox" name="is_active" value="1" <?= $project['is_active'] ? 'checked' : '' ?>>
                <span>Активен на сайте</span>
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="admin-btn admin-btn--primary">
                <i class="bi bi-check2"></i> <?= $isNew ? 'Создать' : 'Сохранить' ?>
            </button>
            <a href="projects.php" class="admin-btn admin-btn--outline">Отмена</a>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- GALLERY -->
<?php if ($tab === 'gallery' && !$isNew): ?>
<div class="admin-card">
    <h2 class="admin-card-title">Галерея проекта</h2>

    <form method="post" enctype="multipart/form-data" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_gallery">

        <div class="form-row">
            <label>Загрузить фото (можно несколько)</label>
            <input type="file" name="gallery_files[]" accept="image/*" multiple>
        </div>

        <div class="form-row">
            <label>Или ссылки (по одной на строку)</label>
            <textarea name="gallery_urls" rows="6" placeholder="assets/uploads/projects/1.jpg"><?= h(implode("\n", $gallery)) ?></textarea>
        </div>

        <?php if ($gallery): ?>
        <div class="gallery-preview">
            <?php foreach ($gallery as $img): ?>
                <div class="gallery-preview__item">
                    <img src="../<?= h($img) ?>" alt="">
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="form-actions">
            <button type="submit" class="admin-btn admin-btn--primary">
                <i class="bi bi-check2"></i> Сохранить галерею
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- MATERIALS -->
<?php if ($tab === 'materials' && !$isNew): ?>
<div class="admin-card">
    <h2 class="admin-card-title">Материалы проекта</h2>

    <form method="post" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_materials">

        <div id="materialsList" class="repeat-list">
            <?php foreach ($materials as $i => $m): ?>
                <div class="repeat-item">
                    <div class="repeat-fields">
                        <div class="form-row">
                            <label>Название</label>
                            <input type="text" name="materials[<?= $i ?>][name]" value="<?= h($m['name']) ?>" required>
                        </div>
                        <div class="form-row">
                            <label>Бренд</label>
                            <input type="text" name="materials[<?= $i ?>][brand]" value="<?= h($m['brand']) ?>">
                        </div>
                        <div class="form-row">
                            <label>Цвет</label>
                            <input type="color" name="materials[<?= $i ?>][color_code]" value="<?= h($m['color_code'] ?: '#cccccc') ?>">
                        </div>
                    </div>
                    <button type="button" class="repeat-remove" onclick="this.closest('.repeat-item').remove()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="admin-btn admin-btn--outline" id="addMaterial">
            <i class="bi bi-plus-lg"></i> Добавить материал
        </button>

        <div class="form-actions mt-3">
            <button type="submit" class="admin-btn admin-btn--primary">
                <i class="bi bi-check2"></i> Сохранить материалы
            </button>
        </div>
    </form>
</div>

<template id="materialTemplate">
    <div class="repeat-item">
        <div class="repeat-fields">
            <div class="form-row">
                <label>Название</label>
                <input type="text" name="materials[__i__][name]" required>
            </div>
            <div class="form-row">
                <label>Бренд</label>
                <input type="text" name="materials[__i__][brand]">
            </div>
            <div class="form-row">
                <label>Цвет</label>
                <input type="color" name="materials[__i__][color_code]" value="#cccccc">
            </div>
        </div>
        <button type="button" class="repeat-remove" onclick="this.closest('.repeat-item').remove()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</template>
<?php endif; ?>

<!-- FURNITURE -->
<?php if ($tab === 'furniture' && !$isNew): ?>
<div class="admin-card">
    <h2 class="admin-card-title">Мебель проекта</h2>

    <form method="post" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_furniture">

        <div id="furnitureList" class="repeat-list">
            <?php foreach ($furniture as $i => $f): ?>
                <div class="repeat-item">
                    <div class="repeat-fields">
                        <div class="form-row">
                            <label>Название</label>
                            <input type="text" name="furniture[<?= $i ?>][name]" value="<?= h($f['name']) ?>" required>
                        </div>
                        <div class="form-row">
                            <label>Описание</label>
                            <input type="text" name="furniture[<?= $i ?>][description]" value="<?= h($f['description']) ?>">
                        </div>
                        <div class="form-row">
                            <label>Фото (URL)</label>
                            <input type="text" name="furniture[<?= $i ?>][image_url]" value="<?= h($f['image_url']) ?>">
                        </div>
                        <div class="form-row">
                            <label>Цена</label>
                            <input type="text" name="furniture[<?= $i ?>][price]" value="<?= h($f['price']) ?>">
                        </div>
                        <div class="form-row">
                            <label>Ссылка</label>
                            <input type="text" name="furniture[<?= $i ?>][link]" value="<?= h($f['link']) ?>">
                        </div>
                    </div>
                    <button type="button" class="repeat-remove" onclick="this.closest('.repeat-item').remove()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="admin-btn admin-btn--outline" id="addFurniture">
            <i class="bi bi-plus-lg"></i> Добавить мебель
        </button>

        <div class="form-actions mt-3">
            <button type="submit" class="admin-btn admin-btn--primary">
                <i class="bi bi-check2"></i> Сохранить мебель
            </button>
        </div>
    </form>
</div>

<template id="furnitureTemplate">
    <div class="repeat-item">
        <div class="repeat-fields">
            <div class="form-row">
                <label>Название</label>
                <input type="text" name="furniture[__i__][name]" required>
            </div>
            <div class="form-row">
                <label>Описание</label>
                <input type="text" name="furniture[__i__][description]">
            </div>
            <div class="form-row">
                <label>Фото (URL)</label>
                <input type="text" name="furniture[__i__][image_url]">
            </div>
            <div class="form-row">
                <label>Цена</label>
                <input type="text" name="furniture[__i__][price]">
            </div>
            <div class="form-row">
                <label>Ссылка</label>
                <input type="text" name="furniture[__i__][link]">
            </div>
        </div>
        <button type="button" class="repeat-remove" onclick="this.closest('.repeat-item').remove()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</template>
<?php endif; ?>

<!-- FEATURES -->
<?php if ($tab === 'features' && !$isNew): ?>
<div class="admin-card">
    <h2 class="admin-card-title">Особенности проекта</h2>

    <form method="post" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_features">

        <div id="featuresList" class="repeat-list">
            <?php foreach ($features as $i => $f): ?>
                <div class="repeat-item">
                    <div class="repeat-fields">
                        <div class="form-row">
                            <label>Текст</label>
                            <input type="text" name="features[<?= $i ?>][feature]" value="<?= h($f['feature']) ?>" required>
                        </div>
                        <div class="form-row">
                            <label>Иконка</label>
                            <input type="text" name="features[<?= $i ?>][icon]" value="<?= h($f['icon']) ?>">
                        </div>
                    </div>
                    <button type="button" class="repeat-remove" onclick="this.closest('.repeat-item').remove()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="admin-btn admin-btn--outline" id="addFeature">
            <i class="bi bi-plus-lg"></i> Добавить особенность
        </button>

        <div class="form-actions mt-3">
            <button type="submit" class="admin-btn admin-btn--primary">
                <i class="bi bi-check2"></i> Сохранить особенности
            </button>
        </div>
    </form>
</div>

<template id="featureTemplate">
    <div class="repeat-item">
        <div class="repeat-fields">
            <div class="form-row">
                <label>Текст</label>
                <input type="text" name="features[__i__][feature]" required>
            </div>
            <div class="form-row">
                <label>Иконка</label>
                <input type="text" name="features[__i__][icon]" value="fa-regular fa-circle-check">
            </div>
        </div>
        <button type="button" class="repeat-remove" onclick="this.closest('.repeat-item').remove()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</template>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function bindAdd(btnId, listId, tplId, prefix) {
        const btn  = document.getElementById(btnId);
        const list = document.getElementById(listId);
        const tpl  = document.getElementById(tplId);
        if (!btn || !list || !tpl) return;

        btn.addEventListener('click', function () {
            const idx = list.children.length;
            const html = tpl.innerHTML.replace(/__i__/g, idx);
            const div = document.createElement('div');
            div.innerHTML = html.trim();
            list.appendChild(div.firstChild);
        });
    }

    bindAdd('addMaterial',  'materialsList', 'materialTemplate',  'materials');
    bindAdd('addFurniture', 'furnitureList', 'furnitureTemplate', 'furniture');
    bindAdd('addFeature',   'featuresList',  'featureTemplate',   'features');
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>