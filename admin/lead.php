<?php
$pageTitle = 'Заявка';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('leads.php');

$stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$lead = $stmt->fetch();

if (!$lead) redirect('leads.php');

$statuses = [
    'new'       => 'Новая',
    'contacted' => 'В работе',
    'completed' => 'Завершена',
    'cancelled' => 'Отменена',
];

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $data = [
            'name'    => trim($_POST['name'] ?? ''),
            'phone'   => trim($_POST['phone'] ?? ''),
            'email'   => trim($_POST['email'] ?? ''),
            'source'  => trim($_POST['source'] ?? ''),
            'status'  => $_POST['status'] ?? 'new',
            'comment' => trim($_POST['comment'] ?? ''),
        ];

        db_update($pdo, 'leads', $data, 'id = :id', ['id' => $id]);
        $success = 'Заявка сохранена';

        $stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $lead = $stmt->fetch();
    }

    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM leads WHERE id = ?")->execute([$id]);
        redirect('leads.php');
    }
}

// Данные по проекту, если есть
$projectInfo = null;
if (!empty($lead['project_info'])) {
    $decoded = json_decode($lead['project_info'], true);
    if (is_array($decoded)) $projectInfo = $decoded;
}

$quizData = null;
if (!empty($lead['quiz_data'])) {
    $decoded = json_decode($lead['quiz_data'], true);
    if (is_array($decoded) && $decoded) $quizData = $decoded;
}
?>

<div class="lead-page">

    <div class="lead-page-head">
        <a href="leads.php" class="admin-back">
            <i class="bi bi-arrow-left"></i> Все заявки
        </a>

        <div class="lead-page-actions mb-2">
            <form method="post" class="inline-form" onsubmit="return confirm('Удалить заявку?')">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <button class="admin-btn admin-btn--danger">
                    <i class="bi bi-trash"></i> Удалить
                </button>
            </form>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="admin-alert admin-alert--success">
            <i class="bi bi-check2-circle"></i> <?= h($success) ?>
        </div>
    <?php endif; ?>

    <div class="lead-page-grid">

        <!-- ЛЕВАЯ КОЛОНКА — форма -->
        <div class="admin-card">
            <h2 class="admin-card-title">Данные заявки</h2>

            <form method="post" class="admin-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">

                <div class="form-row">
                    <label>Имя</label>
                    <input type="text" name="name" value="<?= h($lead['name']) ?>" required>
                </div>

                <div class="form-row">
                    <label>Телефон</label>
                    <input type="tel" name="phone" value="<?= h($lead['phone']) ?>" required>
                </div>

                <div class="form-row">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= h($lead['email']) ?>">
                </div>

                <div class="form-row">
                    <label>Источник</label>
                    <input type="text" name="source" value="<?= h($lead['source']) ?>">
                </div>

                <div class="form-row">
                    <label>Статус</label>
                    <select name="status">
                        <?php foreach ($statuses as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $lead['status'] === $key ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <label>Комментарий менеджера</label>
                    <textarea name="comment" rows="5"><?= h($lead['comment']) ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="admin-btn admin-btn--primary">
                        <i class="bi bi-check2"></i> Сохранить
                    </button>
                </div>
            </form>
        </div>

        <!-- ПРАВАЯ КОЛОНКА — мета -->
        <div class="lead-page-side">

            <div class="admin-card">
                <h2 class="admin-card-title">Сводка</h2>
                <div class="meta-list">
                    <div class="meta-row">
                        <span>ID заявки</span>
                        <strong>#<?= (int)$lead['id'] ?></strong>
                    </div>
                    <div class="meta-row">
                        <span>Создана</span>
                        <strong><?= fmt_date($lead['created_at']) ?></strong>
                    </div>
                    <div class="meta-row">
                        <span>Обновлена</span>
                        <strong><?= fmt_date($lead['updated_at']) ?></strong>
                    </div>
                    <div class="meta-row">
                        <span>Статус</span>
                        <span class="status-badge status-<?= h($lead['status']) ?>">
                            <?= h(status_label($lead['status'])) ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if ($projectInfo): ?>
            <div class="admin-card">
                <h2 class="admin-card-title">Источник — проект</h2>
                <div class="meta-list">
                    <?php if (!empty($projectInfo['title'])): ?>
                    <div class="meta-row">
                        <span>Проект</span>
                        <strong><?= h($projectInfo['title']) ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($projectInfo['id'])): ?>
                    <div class="meta-row">
                        <span>ID проекта</span>
                        <strong>#<?= (int)$projectInfo['id'] ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($projectInfo['url'])): ?>
                    <div class="meta-row">
                        <span>URL</span>
                        <a href="<?= h($projectInfo['url']) ?>" target="_blank" class="admin-link-inline">
                            открыть <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($quizData): ?>
            <div class="admin-card">
                <h2 class="admin-card-title">Данные квиза</h2>
                <div class="meta-list">
                    <?php foreach ($quizData as $k => $v): ?>
                    <div class="meta-row">
                        <span><?= h($k) ?></span>
                        <strong><?= h(is_array($v) ? implode(', ', $v) : $v) ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="admin-card">
                <h2 class="admin-card-title">Быстрые действия</h2>
                <div class="quick-actions">
                    <a href="tel:<?= h(preg_replace('/\D/', '', $lead['phone'])) ?>" class="admin-btn admin-btn--outline w-100">
                        <i class="bi bi-telephone"></i> Позвонить
                    </a>
                    <a href="https://wa.me/<?= h(preg_replace('/\D/', '', $lead['phone'])) ?>" target="_blank" class="admin-btn admin-btn--outline w-100">
                        <i class="bi bi-whatsapp"></i> WhatsApp
                    </a>
                    <a href="https://t.me/<?= h(preg_replace('/\D/', '', $lead['phone'])) ?>" target="_blank" class="admin-btn admin-btn--outline w-100">
                        <i class="bi bi-telegram"></i> Telegram
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>