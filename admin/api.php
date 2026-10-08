<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!admin_user()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'unauthorized']);
    exit;
}

function json_ok(array $data = []): void {
    echo json_encode(['ok' => true] + $data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_err(string $error, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

// ---------- CSRF ----------
$csrf = $_POST['_csrf'] ?? $_GET['_csrf'] ?? '';
if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    json_err('csrf', 419);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    /* ============================================================
       ЗАЯВКИ
       ============================================================ */

    case 'lead.update_status': {
        $id     = (int)($_POST['lead_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!$id || !in_array($status, ['new','contacted','completed','cancelled'], true)) {
            json_err('invalid');
        }

        db_update($pdo, 'leads', ['status' => $status], 'id = :id', ['id' => $id]);

        // HTML карточки для канбана + строки для таблицы
        ob_start();
        render_lead_kanban_card($pdo, $id);
        $kanban = ob_get_clean();

        ob_start();
        render_lead_table_row($pdo, $id);
        $row = ob_get_clean();

        json_ok([
            'lead_id' => $id,
            'status'  => $status,
            'html'    => ['kanban' => $kanban, 'row' => $row],
        ]);
    }

    case 'lead.save': {
        $id   = (int)($_POST['id'] ?? 0);
        if (!$id) json_err('invalid_id');

        $data = [
            'name'    => trim($_POST['name'] ?? ''),
            'phone'   => trim($_POST['phone'] ?? ''),
            'email'   => trim($_POST['email'] ?? ''),
            'source'  => trim($_POST['source'] ?? ''),
            'status'  => $_POST['status'] ?? 'new',
            'comment' => trim($_POST['comment'] ?? ''),
        ];

        if ($data['name'] === '' || $data['phone'] === '') {
            json_err('Имя и телефон обязательны');
        }

        db_update($pdo, 'leads', $data, 'id = :id', ['id' => $id]);

        json_ok([
            'lead_id' => $id,
            'message' => 'Заявка сохранена',
        ]);
    }

    case 'lead.delete': {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_err('invalid_id');

        $pdo->prepare("DELETE FROM leads WHERE id = ?")->execute([$id]);

        json_ok(['lead_id' => $id, 'message' => 'Заявка удалена']);
    }

    /* ============================================================
       ПРОЕКТЫ
       ============================================================ */

    case 'project.toggle_active': {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_err('invalid_id');

        $pdo->prepare("UPDATE projects SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
        $row = dbRow($pdo, "SELECT is_active FROM projects WHERE id = ?", [$id]);

        json_ok([
            'id'     => $id,
            'active' => (int)($row['is_active'] ?? 0),
        ]);
    }

    case 'project.delete': {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_err('invalid_id');

        $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);

        json_ok(['id' => $id, 'message' => 'Проект удалён']);
    }

    case 'project.save_main': {
        $id = (int)($_POST['id'] ?? 0);

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
            'is_active'      => !empty($_POST['is_active']) ? 1 : 0,
        ];

        if ($data['title'] === '') json_err('Название обязательно');

        // Превью
        if (!empty($_FILES['image_file']['tmp_name'])) {
            $url = upload_image($_FILES['image_file'], 'projects');
            if ($url) $data['image_url'] = $url;
        } elseif (!empty($_POST['image_url'])) {
            $data['image_url'] = trim($_POST['image_url']);
        }

        // До / После
        if (!empty($_FILES['before_file']['tmp_name'])) {
            $url = upload_image($_FILES['before_file'], 'projects/before');
            if ($url) $data['before_image'] = $url;
        }
        if (!empty($_FILES['after_file']['tmp_name'])) {
            $url = upload_image($_FILES['after_file'], 'projects/after');
            if ($url) $data['after_image'] = $url;
        }

        if ($id) {
            db_update($pdo, 'projects', $data, 'id = :id', ['id' => $id]);
            json_ok(['id' => $id, 'message' => 'Сохранено']);
        } else {
            $newId = db_insert($pdo, 'projects', $data);
            json_ok(['id' => $newId, 'redirect' => 'project-edit.php?id=' . $newId, 'message' => 'Проект создан']);
        }
    }

    case 'project.save_materials': {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_err('invalid_id');

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

        json_ok(['message' => 'Материалы сохранены']);
    }

    case 'project.save_furniture': {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_err('invalid_id');

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

        json_ok(['message' => 'Мебель сохранена']);
    }

    case 'project.save_features': {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_err('invalid_id');

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

        json_ok(['message' => 'Особенности сохранены']);
    }

    case 'project.save_gallery': {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_err('invalid_id');

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

        $json = json_encode($urls, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        db_update($pdo, 'projects', ['gallery_images' => $json], 'id = :id', ['id' => $id]);

        json_ok([
            'gallery' => $urls,
            'message' => 'Галерея сохранена',
        ]);
    }

    /* ============================================================
       НАСТРОЙКИ
       ============================================================ */

    case 'settings.change_password': {
        $user    = admin_user();
        $current = (string)($_POST['current_password'] ?? '');
        $new     = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$user['id']]);
        $dbPass = $stmt->fetchColumn();

        if ($dbPass !== $current) json_err('Текущий пароль неверен');
        if (strlen($new) < 6) json_err('Минимум 6 символов');
        if ($new !== $confirm) json_err('Пароли не совпадают');

        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$new, $user['id']]);

        json_ok(['message' => 'Пароль обновлён']);
    }
    /* ============================================================
       ИНТЕРЬЕРЫ
       ============================================================ */

    case 'interior.save': {
        $id = (int)($_POST['id'] ?? 0);

        $data = [
            'title'       => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'type'        => trim($_POST['type'] ?? ''),
            'area'        => trim($_POST['area'] ?? ''),
            'style'       => trim($_POST['style'] ?? ''),
            'sort_order'  => (int)($_POST['sort_order'] ?? 0),
            'is_active'   => !empty($_POST['is_active']) ? 1 : 0,
        ];

        if ($data['title'] === '') json_err('Название обязательно');

        if (!empty($_FILES['image_file']['tmp_name'])) {
            $url = upload_image($_FILES['image_file'], 'interiors');
            if ($url) $data['image_url'] = $url;
        } elseif (!empty($_POST['image_url'])) {
            $data['image_url'] = trim($_POST['image_url']);
        }

        if ($id) {
            db_update($pdo, 'interiors', $data, 'id = :id', ['id' => $id]);
            json_ok(['id' => $id, 'message' => 'Интерьер сохранён']);
        } else {
            $newId = db_insert($pdo, 'interiors', $data);
            json_ok(['id' => $newId, 'redirect' => 'interiors-edit.php?id=' . $newId, 'message' => 'Интерьер создан']);
        }
    }

    case 'interior.delete': {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_err('invalid_id');

        $pdo->prepare("DELETE FROM interiors WHERE id = ?")->execute([$id]);
        json_ok(['id' => $id, 'message' => 'Интерьер удалён']);
    }

    case 'interior.toggle_active': {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_err('invalid_id');

        $pdo->prepare("UPDATE interiors SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
        $row = dbRow($pdo, "SELECT is_active FROM interiors WHERE id = ?", [$id]);

        json_ok(['id' => $id, 'active' => (int)($row['is_active'] ?? 0)]);
    }
    default:
        json_err('unknown_action');
}



/* ============================================================
   РЕНДЕР-ХЕЛПЕРЫ
   ============================================================ */

function render_lead_kanban_card(PDO $pdo, int $id): void {
    $lead = dbRow($pdo, "SELECT * FROM leads WHERE id = ?", [$id]);
    if (!$lead) return;

    $phoneDigits = preg_replace('/\D/', '', $lead['phone'] ?? '');
    ?>
    <div class="kanban-card" data-id="<?= (int)$lead['id'] ?>">
        <div class="kanban-card-head">
            <strong><?= h($lead['name']) ?></strong>
            <div class="kanban-card-actions">
                <a href="lead.php?id=<?= (int)$lead['id'] ?>" class="icon-btn" title="Открыть">
                    <i class="bi bi-pencil"></i>
                </a>
            </div>
        </div>
        <a href="tel:<?= h($phoneDigits) ?>" class="kanban-phone">
            <i class="bi bi-telephone"></i> <?= h($lead['phone']) ?>
        </a>
        <?php if (!empty($lead['source'])): ?>
        <div class="kanban-source"><?= h($lead['source']) ?></div>
        <?php endif; ?>
        <div class="kanban-footer">
            <span><?= fmt_date($lead['created_at'], 'd.m H:i') ?></span>
        </div>
    </div>
    
    <?php
}


function render_lead_table_row(PDO $pdo, int $id): void {
    $lead = dbRow($pdo, "SELECT * FROM leads WHERE id = ?", [$id]);
    if (!$lead) return;

    $phoneDigits = preg_replace('/\D/', '', $lead['phone'] ?? '');
    $statuses = [
        'new'       => 'Новая',
        'contacted' => 'В работе',
        'completed' => 'Завершена',
        'cancelled' => 'Отменена',
    ];
    ?>
    <tr data-id="<?= (int)$lead['id'] ?>">
        <td class="td-mono"><?= (int)$lead['id'] ?></td>
        <td><strong><?= h($lead['name']) ?></strong></td>
        <td><a href="tel:<?= h($phoneDigits) ?>"><?= h($lead['phone']) ?></a></td>
        <td><small><?= h($lead['source'] ?: '—') ?></small></td>
        <td>
            <select class="status-select js-lead-status" data-id="<?= (int)$lead['id'] ?>">
                <?php foreach ($statuses as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $lead['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td class="td-mono"><?= fmt_date($lead['created_at'], 'd.m.Y H:i') ?></td>
        <td class="td-actions">
            <a href="lead.php?id=<?= (int)$lead['id'] ?>" class="icon-btn"><i class="bi bi-pencil"></i></a>
            <button class="icon-btn icon-btn--danger js-lead-delete" data-id="<?= (int)$lead['id'] ?>">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
    <?php
}