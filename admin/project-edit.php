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
$estimate = null;
$estimateCategories = [];
$estimateCatalog = [];
$estimateDirect = 0.0;
$estimateCoef = 1.0;
if (!$isNew) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS estimate_projects (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, project_id INT UNSIGNED NULL, title VARCHAR(255) NOT NULL, client_name VARCHAR(255) NULL, city VARCHAR(120) NULL, status VARCHAR(30) NOT NULL DEFAULT 'draft', coefficient DECIMAL(12,4) NOT NULL DEFAULT 1.0000, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, KEY project_id(project_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $cols=$pdo->query("SHOW COLUMNS FROM estimate_projects LIKE 'project_id'")->fetchAll();
    if(!$cols){$pdo->exec("ALTER TABLE estimate_projects ADD COLUMN project_id INT UNSIGNED NULL AFTER id");$pdo->exec("ALTER TABLE estimate_projects ADD KEY project_id(project_id)");}
    $pdo->exec("CREATE TABLE IF NOT EXISTS estimate_categories (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, estimate_id INT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, sort_order INT NOT NULL DEFAULT 0, KEY estimate_id(estimate_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS estimate_items (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, category_id INT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, quantity DECIMAL(14,3) NOT NULL DEFAULT 1, unit VARCHAR(30) NOT NULL DEFAULT 'шт', price DECIMAL(14,2) NOT NULL DEFAULT 0, sort_order INT NOT NULL DEFAULT 0, KEY category_id(category_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $stmt=$pdo->prepare("SELECT * FROM estimate_projects WHERE project_id=? ORDER BY id ASC LIMIT 1");$stmt->execute([$id]);$estimate=$stmt->fetch();
    if(!$estimate){$pdo->prepare("INSERT INTO estimate_projects(project_id,title,status,coefficient) VALUES(?,?,?,1.0000)")->execute([$id,$project['title']?:'Смета проекта','draft']);$eid=(int)$pdo->lastInsertId();$pdo->prepare("INSERT INTO estimate_categories(estimate_id,name,sort_order) VALUES(?,?,0)")->execute([$eid,'Материалы и работы']);$stmt->execute([$id]);$estimate=$stmt->fetch();}
    if($estimate){$stmt=$pdo->prepare("SELECT * FROM estimate_categories WHERE estimate_id=? ORDER BY sort_order ASC,id ASC");$stmt->execute([(int)$estimate['id']]);$estimateCategories=$stmt->fetchAll();foreach($estimateCategories as &$ec){$stmt=$pdo->prepare("SELECT * FROM estimate_items WHERE category_id=? ORDER BY sort_order ASC,id ASC");$stmt->execute([(int)$ec['id']]);$ec['items']=$stmt->fetchAll();foreach($ec['items'] as $ei)$estimateDirect+=(float)$ei['quantity']*(float)$ei['price'];}unset($ec);$estimateCoef=max(.001,(float)$estimate['coefficient']);}
    try{$estimateCatalog=$pdo->query("SELECT * FROM quick_estimate_catalog WHERE active=1 ORDER BY FIELD(category,'materials','countertop','hardware','fasteners'),name")->fetchAll();}catch(Throwable $e){$estimateCatalog=[];}
}

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

    // 6. Смета проекта
    if (!$isNew && in_array($action,['save_estimate','add_estimate_category','delete_estimate_category','add_estimate_item','update_estimate_item','delete_estimate_item','save_estimate_coefficient','add_estimate_catalog'],true)) {
        $stmt=$pdo->prepare("SELECT * FROM estimate_projects WHERE project_id=? LIMIT 1");$stmt->execute([$id]);$er=$stmt->fetch();$eid=(int)($er['id']??0);
        if(!$eid){$pdo->prepare("INSERT INTO estimate_projects(project_id,title,status,coefficient) VALUES(?,?,?,1.0000)")->execute([$id,$project['title']?:'Смета проекта','draft']);$eid=(int)$pdo->lastInsertId();$pdo->prepare("INSERT INTO estimate_categories(estimate_id,name,sort_order) VALUES(?,?,0)")->execute([$eid,'Материалы и работы']);}
        if($action==='save_estimate'){$pdo->prepare("UPDATE estimate_projects SET title=?,client_name=?,city=?,status=? WHERE id=?")->execute([trim($_POST['estimate_title']??''),trim($_POST['estimate_client_name']??''),trim($_POST['estimate_city']??''),trim($_POST['estimate_status']??'draft'),$eid]);}
        if($action==='save_estimate_coefficient'){$coef=max(.001,(float)str_replace(',','.',$_POST['coefficient']??'1'));$pdo->prepare("UPDATE estimate_projects SET coefficient=? WHERE id=?")->execute([$coef,$eid]);if(!empty($_POST['ajax'])){header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true]);exit;}}
        if($action==='add_estimate_category'){$name=trim($_POST['name']??'');if($name!==''){$pdo->prepare("INSERT INTO estimate_categories(estimate_id,name,sort_order) SELECT ?,?,COALESCE(MAX(sort_order)+1,0) FROM estimate_categories WHERE estimate_id=?")->execute([$eid,$name,$eid]);}}
        if($action==='delete_estimate_category'){$cid=(int)$_POST['category_id'];$pdo->prepare("DELETE FROM estimate_items WHERE category_id=?")->execute([$cid]);$pdo->prepare("DELETE FROM estimate_categories WHERE id=? AND estimate_id=?")->execute([$cid,$eid]);}
        if($action==='add_estimate_item'){$cid=(int)$_POST['category_id'];$pdo->prepare("INSERT INTO estimate_items(category_id,name,quantity,unit,price,sort_order) VALUES(?,?,?,?,?,0)")->execute([$cid,trim($_POST['name']??''),max(.001,(float)str_replace(',','.',$_POST['quantity']??'1')),trim($_POST['unit']??'шт'),max(0,(float)str_replace(',','.',$_POST['price']??'0'))]);}
        if($action==='update_estimate_item'){$iid=(int)$_POST['item_id'];$pdo->prepare("UPDATE estimate_items i JOIN estimate_categories c ON c.id=i.category_id SET i.name=?,i.quantity=?,i.unit=?,i.price=? WHERE i.id=? AND c.estimate_id=?")->execute([trim($_POST['name']??''),max(.001,(float)str_replace(',','.',$_POST['quantity']??'1')),trim($_POST['unit']??'шт'),max(0,(float)str_replace(',','.',$_POST['price']??'0')),$iid,$eid]);}
        if($action==='delete_estimate_item'){$iid=(int)$_POST['item_id'];$pdo->prepare("DELETE i FROM estimate_items i JOIN estimate_categories c ON c.id=i.category_id WHERE i.id=? AND c.estimate_id=?")->execute([$iid,$eid]);}
        if($action==='add_estimate_catalog'){$cid=(int)$_POST['category_id'];$catalogId=(int)$_POST['catalog_id'];$qty=max(.001,(float)str_replace(',','.',$_POST['quantity']??'1'));$st=$pdo->prepare("SELECT name,unit,price FROM quick_estimate_catalog WHERE id=? AND active=1 LIMIT 1");$st->execute([$catalogId]);$ci=$st->fetch();if($ci)$pdo->prepare("INSERT INTO estimate_items(category_id,name,quantity,unit,price,sort_order) VALUES(?,?,?,?,?,0)")->execute([$cid,$ci['name'],$qty,$ci['unit'],$ci['price']]);}
        redirect('project-edit.php?id='.$id.'&tab=estimate');
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
    'estimate'  => 'Смета',
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

<!-- ESTIMATE -->
<?php if ($tab==='estimate' && !$isNew && $estimate): ?>
<?php $base=$estimateDirect*$estimateCoef;$overhead=$base*.15;$profit=$base*.08;$vat=($base+$overhead+$profit)*.20;$grand=$base+$overhead+$profit+$vat;$estimateLabels=['materials'=>'ЛДСП / МДФ / Кромка','countertop'=>'Столешницы','hardware'=>'Фурнитура','fasteners'=>'Крепёж и расходники']; ?>
<div class="smeta-page project-smeta-page"><div class="smeta-heading"><div><a href="?id=<?=$id?>&tab=main" class="smeta-back"><i class="bi bi-arrow-left"></i> К проекту</a><div class="smeta-title-row"><div class="smeta-icon"><i class="bi bi-calculator"></i></div><div><div class="smeta-eyebrow">СМЕТА ПРОЕКТА</div><h2><?=h($project['title'])?></h2><p>Смета является частью этого проекта</p></div></div></div><div class="smeta-heading-actions"><button class="admin-btn admin-btn--outline" data-bs-toggle="modal" data-bs-target="#projectEstimateSettings"><i class="bi bi-sliders"></i> Параметры</button><button class="admin-btn admin-btn--primary" data-bs-toggle="modal" data-bs-target="#addEstimateCategory"><i class="bi bi-plus-lg"></i> Раздел</button></div></div>
<div class="smeta-summary"><div><span>ИТОГО</span><strong id="projectSmetaGrand"><?=number_format($grand,2,',',' ')?> ₽</strong><small>с НДС</small></div><div class="smeta-summary-stats"><div><b><?=count($estimateCategories)?></b> разделов</div><div><b><?=array_sum(array_map(fn($x)=>count($x['items']),$estimateCategories))?></b> позиций</div><div><b id="projectSmetaCoef">× <?=number_format($estimateCoef,3,',',' ')?></b> коэффициент</div></div></div>
<div class="smeta-toolbar"><div><strong>Состав сметы</strong><span>Позиции проекта</span></div></div><div class="smeta-estimate-list">
<?php foreach($estimateCategories as $gi=>$group):$groupTotal=0;foreach($group['items'] as $it)$groupTotal+=(float)$it['quantity']*(float)$it['price'];?><section class="estimate-group"><div class="group-heading"><span class="group-number"><?=str_pad((string)($gi+1),2,'0',STR_PAD_LEFT)?></span><h3><?=h($group['name'])?></h3><span><?=count($group['items'])?> поз.</span><strong><?=number_format($groupTotal,2,',',' ')?> ₽</strong><button class="smeta-row-more" data-bs-toggle="modal" data-bs-target="#delCat<?=$group['id']?>"><i class="bi bi-three-dots"></i></button></div><div class="estimate-table"><div class="table-head"><span>Позиция</span><span>Количество</span><span>Ед.</span><span>Цена</span><span>Сумма</span><span></span></div>
<?php foreach($group['items'] as $item):$sum=(float)$item['quantity']*(float)$item['price'];?><div class="table-row smeta-row"><form method="post" class="inline-edit-form"><?=csrf_field()?><input type="hidden" name="action" value="update_estimate_item"><input type="hidden" name="item_id" value="<?=$item['id']?>"><input type="hidden" name="name"><input type="hidden" name="quantity"><input type="hidden" name="unit"><input type="hidden" name="price"></form><span class="work-name rv-name"><span class="work-dot"></span><?=h($item['name'])?></span><input class="cell-input re-name" value="<?=h($item['name'])?>" hidden><span class="rv-qty"><?=number_format($item['quantity'],3,',',' ')?></span><input class="cell-input re-qty" type="number" step=".001" value="<?=h($item['quantity'])?>" hidden><span class="rv-unit"><?=h($item['unit'])?></span><input class="cell-input re-unit" value="<?=h($item['unit'])?>" hidden><span class="rv-price"><?=number_format($item['price'],2,',',' ')?> ₽</span><input class="cell-input re-price" type="number" step=".01" value="<?=h($item['price'])?>" hidden><span class="rv-sum"><?=number_format($sum,2,',',' ')?> ₽</span><span class="row-actions"><button type="button" class="edit-estimate-row"><i class="bi bi-pencil"></i></button><button type="button" class="delete-estimate-row" data-id="<?=$item['id']?>"><i class="bi bi-trash"></i></button><button type="button" class="save-estimate-row" hidden><i class="bi bi-check2"></i></button><button type="button" class="cancel-estimate-row" hidden><i class="bi bi-x"></i></button></span></div><?php endforeach;?><div><button type="button" class="smeta-add-row add-estimate-manual" data-cat="<?=$group['id']?>" data-bs-toggle="modal" data-bs-target="#addEstimateItem"><i class="bi bi-plus-lg"></i> Добавить позицию</button><button type="button" class="smeta-add-catalog add-estimate-catalog" data-cat="<?=$group['id']?>" data-bs-toggle="modal" data-bs-target="#estimateCatalog"><i class="bi bi-box-seam"></i> Из каталога</button></div></div><div class="modal fade" id="delCat<?=$group['id']?>"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="delete_estimate_category"><input type="hidden" name="category_id" value="<?=$group['id']?>"><div class="modal-header"><h5 class="modal-title">Удалить раздел?</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">Раздел и все позиции будут удалены.</div><div class="modal-footer"><button type="button" class="admin-btn admin-btn--outline" data-bs-dismiss="modal">Отмена</button><button class="admin-btn admin-btn--primary">Удалить</button></div></form></div></div></div></section><?php endforeach;?></div>
<div class="estimate-totals smeta-totals"><div><span>Прямые затраты</span><b id="projectDirect"><?=number_format($estimateDirect,2,',',' ')?> ₽</b></div><div><span>Коэффициент</span><label class="coef-input"><span>×</span><input id="projectCoef" type="number" min=".001" step=".001" value="<?=h($estimateCoef)?>"></label></div><div><span>Затраты с коэффициентом</span><b id="projectBase"><?=number_format($base,2,',',' ')?> ₽</b></div><div><span>Накладные расходы (НР) · 15%</span><b id="projectOver"><?=number_format($overhead,2,',',' ')?> ₽</b></div><div><span>Сметная прибыль (СП) · 8%</span><b id="projectProfit"><?=number_format($profit,2,',',' ')?> ₽</b></div><div><span>НДС · 20%</span><b id="projectVat"><?=number_format($vat,2,',',' ')?> ₽</b></div><div class="grand-total"><span>Итого в текущем уровне цен</span><strong id="projectGrand"><?=number_format($grand,2,',',' ')?> ₽</strong></div></div></div>
<div class="modal fade" id="projectEstimateSettings"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="save_estimate"><div class="modal-header"><h5 class="modal-title">Параметры сметы</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="smeta-form-grid"><label><span>Название сметы</span><input name="estimate_title" value="<?=h($estimate['title'])?>" required></label><label><span>Заказчик</span><input name="estimate_client_name" value="<?=h($estimate['client_name'])?>"></label><label><span>Город</span><input name="estimate_city" value="<?=h($estimate['city'])?>"></label><label><span>Статус</span><select name="estimate_status"><?php foreach(['draft'=>'Черновик','in_progress'=>'В работе','review'=>'На согласовании','completed'=>'Завершена','archived'=>'Архив'] as $v=>$t):?><option value="<?=$v?>" <?=$estimate['status']===$v?'selected':''?>><?=$t?></option><?php endforeach;?></select></label></div></div><div class="modal-footer"><button type="button" class="admin-btn admin-btn--outline" data-bs-dismiss="modal">Отмена</button><button class="admin-btn admin-btn--primary">Сохранить</button></div></form></div></div></div>
<div class="modal fade" id="addEstimateCategory"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="add_estimate_category"><div class="modal-header"><h5 class="modal-title">Новый раздел</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="smeta-field"><span>Название</span><input name="name" required placeholder="Материалы, Фурнитура, Монтаж..."></label></div><div class="modal-footer"><button type="button" class="admin-btn admin-btn--outline" data-bs-dismiss="modal">Отмена</button><button class="admin-btn admin-btn--primary">Создать</button></div></form></div></div></div>
<div class="modal fade" id="addEstimateItem"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="add_estimate_item"><input type="hidden" name="category_id" id="estimateItemCat"><div class="modal-header"><h5 class="modal-title">Добавить позицию</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="smeta-field"><span>Наименование</span><input name="name" required></label><div class="smeta-form-grid"><label><span>Количество</span><input name="quantity" type="number" step=".001" value="1"></label><label><span>Единица</span><input name="unit" value="шт"></label><label><span>Цена</span><input name="price" type="number" step=".01" value="0"></label></div></div><div class="modal-footer"><button type="button" class="admin-btn admin-btn--outline" data-bs-dismiss="modal">Отмена</button><button class="admin-btn admin-btn--primary">Добавить</button></div></form></div></div></div>
<div class="modal fade" id="estimateCatalog"><div class="modal-dialog modal-dialog-centered modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Каталог</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="catalog-search"><i class="bi bi-search"></i><input id="estimateCatalogSearch" placeholder="Поиск материала, фурнитуры, артикула..."></div><div class="catalog-grid"><?php foreach($estimateCatalog as $x):?><button type="button" class="catalog-item" data-id="<?=$x['id']?>" data-search="<?=h(mb_strtolower($x['name'].' '.$x['brand'].' '.$x['article']))?>" data-title="<?=h($x['name'])?>"><span><b><?=h($x['name'])?></b><small><?=h($estimateLabels[$x['category']]??$x['category'])?> · <?=h($x['brand'])?> · <?=h($x['article'])?></small></span><strong><?=number_format($x['price'],2,',',' ')?> ₽/<?=h($x['unit'])?></strong></button><?php endforeach;?></div><div id="estimateCatalogSelected" class="catalog-selected" hidden><span id="estimateCatalogName"></span><label>Количество<input id="estimateCatalogQty" type="number" value="1" min=".001" step=".001"></label></div></div><div class="modal-footer"><button type="button" class="admin-btn admin-btn--outline" data-bs-dismiss="modal">Отмена</button><form method="post" id="estimateCatalogForm"><?=csrf_field()?><input type="hidden" name="action" value="add_estimate_catalog"><input type="hidden" name="category_id" id="estimateCatalogCat"><input type="hidden" name="catalog_id" id="estimateCatalogId"><input type="hidden" name="quantity" id="estimateCatalogQuantity"><button class="admin-btn admin-btn--primary" id="estimateCatalogAdd" disabled>Добавить</button></form></div></div></div></div>
<style>
.project-smeta-page{max-width:1500px}.project-smeta-page .smeta-heading{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:26px}.project-smeta-page .smeta-back{display:inline-flex;gap:7px;color:var(--muted);font-size:11px;text-decoration:none;margin-bottom:14px}.project-smeta-page .smeta-title-row{display:flex;align-items:center;gap:13px}.project-smeta-page .smeta-icon{width:46px;height:46px;border-radius:14px;background:rgba(199,157,109,.12);color:var(--gold-2);display:grid;place-items:center;font-size:20px}.project-smeta-page .smeta-eyebrow{font-size:9px;letter-spacing:.15em;color:var(--gold-2);font-weight:800}.project-smeta-page .smeta-title-row h2{font-size:29px;margin:5px 0;color:var(--ink)}.project-smeta-page .smeta-title-row p{margin:0;color:var(--muted);font-size:11px}.project-smeta-page .smeta-heading-actions{display:flex;gap:8px}.project-smeta-page .smeta-summary{background:linear-gradient(135deg,#24231f,#34312a);border:1px solid rgba(199,157,109,.22);border-radius:var(--r);padding:20px 23px;color:#fff;display:flex;align-items:center;gap:28px;margin-bottom:28px}.project-smeta-page .smeta-summary span,.project-smeta-page .smeta-summary small{display:block;color:#c8bcae;font-size:9px;letter-spacing:.08em}.project-smeta-page .smeta-summary strong{display:block;font:600 27px monospace;margin:7px 0 4px}.project-smeta-page .smeta-summary-stats{display:flex;gap:22px;padding-left:28px;border-left:1px solid rgba(199,157,109,.3)}.project-smeta-page .smeta-summary-stats div{font-size:10px;color:#c8bcae}.project-smeta-page .smeta-summary-stats b{font:600 15px monospace;color:#fff;margin-right:4px}.project-smeta-page .smeta-toolbar{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--line);padding:0 3px 14px;margin-bottom:22px}.project-smeta-page .smeta-toolbar>div{display:flex;gap:12px;align-items:center}.project-smeta-page .smeta-toolbar strong{font-size:12px}.project-smeta-page .smeta-toolbar span{font-size:10px;color:var(--muted)}.project-smeta-page .smeta-estimate-list .estimate-group{margin-bottom:20px}.project-smeta-page .group-heading{display:grid;grid-template-columns:30px 1fr auto auto 34px;align-items:center;gap:9px;padding:0 9px 10px}.project-smeta-page .group-number{font:500 10px monospace;color:var(--gold-2)}.project-smeta-page .group-heading h3{margin:0;font-size:13px}.project-smeta-page .group-heading>span{color:var(--muted);font-size:10px}.project-smeta-page .group-heading>strong{font:500 11px monospace;color:var(--ink)}.project-smeta-page .estimate-table{border:1px solid var(--line);border-radius:12px;overflow:hidden;background:var(--bg-2)}.project-smeta-page .table-head,.project-smeta-page .table-row{display:grid;grid-template-columns:minmax(190px,2fr) 105px 75px 125px 135px 78px;align-items:center;gap:12px;padding:0 15px}.project-smeta-page .table-head{height:35px;background:var(--bg-3);color:var(--muted-2);font:600 9px monospace}.project-smeta-page .table-row{min-height:52px;border-top:1px solid var(--line);color:var(--muted);font:400 11px monospace}.project-smeta-page .work-name{display:flex;align-items:center;gap:8px;color:var(--ink);font:600 11px var(--font)}.project-smeta-page .work-dot{width:5px;height:5px;border-radius:50%;background:var(--muted-2)}.project-smeta-page .cell-input{width:100%;background:var(--bg-3);border:1px solid var(--line-2);border-radius:6px;padding:7px;color:var(--ink)}.project-smeta-page .row-actions{display:flex;justify-content:flex-end;gap:3px}.project-smeta-page .row-actions button{border:0;background:transparent;color:var(--muted);padding:5px}.project-smeta-page .row-actions button:hover{color:var(--gold-2)}.project-smeta-page .smeta-add-row,.project-smeta-page .smeta-add-catalog{display:inline-flex;gap:7px;border:0;background:transparent;padding:13px 15px;font-size:11px;font-weight:700;color:var(--gold-2)}.project-smeta-page .smeta-add-catalog{color:var(--muted)}.project-smeta-page .estimate-totals{margin-top:6px;margin-left:auto;max-width:690px;display:flex;flex-direction:column;border-top:2px solid var(--line-2);padding-top:7px}.project-smeta-page .estimate-totals>div{display:flex;justify-content:space-between;align-items:center;gap:20px;padding:9px 8px;color:var(--muted);font-size:11px}.project-smeta-page .estimate-totals b{font:600 12px monospace;color:var(--ink)}.project-smeta-page .estimate-totals .grand-total{margin-top:5px;padding-top:16px;border-top:1px solid var(--line-2);color:var(--ink)}.project-smeta-page .estimate-totals .grand-total strong{font:700 21px monospace;color:var(--gold-2)}.project-smeta-page .coef-input{display:flex;align-items:center;gap:5px}.project-smeta-page .coef-input input{width:88px;background:var(--bg-3);border:1px solid var(--line-2);border-radius:7px;padding:6px;color:var(--ink);text-align:right}.project-smeta-page .smeta-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.project-smeta-page .smeta-form-grid label,.project-smeta-page .smeta-field{display:grid;gap:7px}.project-smeta-page .smeta-form-grid span,.project-smeta-page .smeta-field span{font-size:10px;text-transform:uppercase;color:var(--muted);font-weight:700}.project-smeta-page .smeta-form-grid input,.project-smeta-page .smeta-form-grid select,.project-smeta-page .smeta-field input{width:100%;background:var(--bg-3);border:1px solid var(--line);border-radius:var(--r-2);padding:11px;color:var(--ink)}.project-smeta-page .catalog-search{display:flex;gap:9px;align-items:center;background:var(--bg-3);border:1px solid var(--line);border-radius:10px;padding:0 12px;margin-bottom:14px}.project-smeta-page .catalog-search input{width:100%;border:0;background:transparent;outline:0;color:var(--ink);padding:11px}.project-smeta-page .catalog-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;max-height:55vh;overflow:auto}.project-smeta-page .catalog-item{display:flex;justify-content:space-between;gap:12px;text-align:left;background:var(--bg-3);border:1px solid var(--line);border-radius:10px;padding:11px;color:var(--ink);width:100%}.project-smeta-page .catalog-item.selected{border-color:var(--gold);background:rgba(199,157,109,.07)}.project-smeta-page .catalog-item b,.project-smeta-page .catalog-item small{display:block}.project-smeta-page .catalog-item b{font-size:11px}.project-smeta-page .catalog-item small{font-size:9px;color:var(--muted);margin-top:4px}.project-smeta-page .catalog-item>strong{white-space:nowrap;font:600 11px monospace}.project-smeta-page .catalog-selected{display:flex;justify-content:space-between;align-items:center;padding:12px;border:1px solid var(--line);margin-top:12px;border-radius:10px}.project-smeta-page .catalog-selected input{width:70px;background:var(--bg-3);border:1px solid var(--line);border-radius:7px;padding:6px;color:var(--ink)}@media(max-width:1000px){.project-smeta-page .smeta-heading{flex-direction:column;align-items:flex-start}.project-smeta-page .smeta-summary{flex-direction:column;align-items:flex-start}.project-smeta-page .smeta-summary-stats{border-left:0;border-top:1px solid rgba(199,157,109,.3);padding:14px 0 0;width:100%}.project-smeta-page .smeta-estimate-list{overflow-x:auto}.project-smeta-page .estimate-table{min-width:760px}.project-smeta-page .catalog-grid{grid-template-columns:1fr}}@media(max-width:650px){.project-smeta-page .smeta-title-row h2{font-size:23px}.project-smeta-page .smeta-heading-actions{width:100%}.project-smeta-page .smeta-heading-actions .admin-btn{flex:1}.project-smeta-page .smeta-form-grid{grid-template-columns:1fr}}
</style>
<script>
(()=>{const n=v=>parseFloat(String(v).replace(',','.'))||0,m=v=>new Intl.NumberFormat('ru-RU',{minimumFractionDigits:2,maximumFractionDigits:2}).format(v)+' ₽';function calc(){let d=0;document.querySelectorAll('.project-smeta-page .smeta-row').forEach(r=>{d+=n(r.querySelector('.re-qty')?.value||r.querySelector('.rv-qty')?.textContent)*n(r.querySelector('.re-price')?.value||r.querySelector('.rv-price')?.textContent)});let c=Math.max(.001,n(document.getElementById('projectCoef')?.value||1)),b=d*c,o=b*.15,p=b*.08,v=(b+o+p)*.2,g=b+o+p+v;[['projectDirect',d],['projectBase',b],['projectOver',o],['projectProfit',p],['projectVat',v],['projectGrand',g]].forEach(([i,x])=>{let e=document.getElementById(i);if(e)e.textContent=m(x)});let e=document.getElementById('projectSmetaGrand');if(e)e.textContent=m(g);e=document.getElementById('projectSmetaCoef');if(e)e.textContent='× '+c.toFixed(3).replace('.',',')}document.getElementById('projectCoef')?.addEventListener('input',calc);document.getElementById('projectCoef')?.addEventListener('change',async e=>{let f=new FormData();f.append('csrf',<?=json_encode(csrf_token())?>);f.append('action','save_estimate_coefficient');f.append('coefficient',e.target.value);f.append('ajax','1');await fetch(location.href,{method:'POST',body:f})});document.querySelectorAll('.edit-estimate-row').forEach(b=>b.onclick=()=>{let r=b.closest('.smeta-row');r.querySelectorAll('.rv-name,.rv-qty,.rv-unit,.rv-price,.rv-sum').forEach(x=>x.hidden=true);r.querySelectorAll('.re-name,.re-qty,.re-unit,.re-price').forEach(x=>x.hidden=false);r.querySelector('.edit-estimate-row').hidden=true;r.querySelector('.delete-estimate-row').hidden=true;r.querySelector('.save-estimate-row').hidden=false;r.querySelector('.cancel-estimate-row').hidden=false});document.querySelectorAll('.cancel-estimate-row').forEach(b=>b.onclick=()=>location.reload());document.querySelectorAll('.save-estimate-row').forEach(b=>b.onclick=()=>{let r=b.closest('.smeta-row'),f=r.querySelector('.inline-edit-form');f.elements.name.value=r.querySelector('.re-name').value;f.elements.quantity.value=r.querySelector('.re-qty').value;f.elements.unit.value=r.querySelector('.re-unit').value;f.elements.price.value=r.querySelector('.re-price').value;f.submit()});document.querySelectorAll('.delete-estimate-row').forEach(b=>b.onclick=()=>{if(confirm('Удалить позицию?')){let f=document.createElement('form');f.method='post';f.innerHTML=<?=json_encode(csrf_field())?>+'<input type="hidden" name="action" value="delete_estimate_item"><input type="hidden" name="item_id" value="'+b.dataset.id+'">';document.body.appendChild(f);f.submit()}});document.querySelectorAll('.add-estimate-manual').forEach(b=>b.onclick=()=>document.getElementById('estimateItemCat').value=b.dataset.cat);document.querySelectorAll('.add-estimate-catalog').forEach(b=>b.onclick=()=>document.getElementById('estimateCatalogCat').value=b.dataset.cat);document.querySelectorAll('.catalog-item').forEach(b=>b.onclick=()=>{document.querySelectorAll('.catalog-item').forEach(x=>x.classList.remove('selected'));b.classList.add('selected');document.getElementById('estimateCatalogId').value=b.dataset.id;document.getElementById('estimateCatalogName').textContent=b.dataset.title;document.getElementById('estimateCatalogSelected').hidden=false;document.getElementById('estimateCatalogAdd').disabled=false});document.getElementById('estimateCatalogQty')?.addEventListener('input',e=>document.getElementById('estimateCatalogQuantity').value=e.target.value);document.getElementById('estimateCatalogForm')?.addEventListener('submit',()=>document.getElementById('estimateCatalogQuantity').value=document.getElementById('estimateCatalogQty').value||1);document.getElementById('estimateCatalogSearch')?.addEventListener('input',e=>{let q=e.target.value.toLowerCase();document.querySelectorAll('.catalog-item').forEach(x=>x.style.display=!q||x.dataset.search.includes(q)?'flex':'none')});calc()})();
</script></div>
<?php endif;?>

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