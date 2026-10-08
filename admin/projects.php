<?php
$pageTitle = 'Проекты';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);
        redirect('projects.php');
    }

    if ($action === 'toggle_active' && $id) {
        $pdo->prepare("UPDATE projects SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
        redirect('projects.php');
    }
}

$projects = $pdo->query("
    SELECT p.*,
        (SELECT COUNT(*) FROM project_materials WHERE project_id = p.id) AS mat_count,
        (SELECT COUNT(*) FROM project_furniture WHERE project_id = p.id) AS furn_count
    FROM projects p
    ORDER BY p.sort_order ASC, p.created_at DESC
")->fetchAll();
?>

<div class="admin-page-head">
    <div></div>
    <a href="project-edit.php" class="admin-btn admin-btn--primary">
        <i class="bi bi-plus-lg"></i> Добавить проект
    </a>
</div>

<div class="admin-card">
    <?php if ($projects): ?>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Превью</th>
                    <th>Название</th>
                    <th>Тип</th>
                    <th>Материалы</th>
                    <th>Просмотры</th>
                    <th>Активен</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $p): ?>
                <tr>
                    <td class="td-mono"><?= (int)$p['id'] ?></td>
                    <td>
                        <?php if (!empty($p['image_url'])): ?>
                            <img src="../<?= h($p['image_url']) ?>" alt="" class="td-thumb">
                        <?php else: ?>
                            <div class="td-thumb td-thumb--empty"><i class="bi bi-image"></i></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= h($p['title']) ?></strong>
                        <?php if (!empty($p['description'])): ?>
                            <small class="td-desc"><?= h(mb_substr($p['description'], 0, 60)) ?>…</small>
                        <?php endif; ?>
                    </td>
                    <td><small><?= h($p['type'] ?: '—') ?></small></td>
                    <td>
                        <small><?= (int)$p['mat_count'] ?> / <?= (int)$p['furn_count'] ?></small>
                    </td>
                    <td><small><i class="bi bi-eye"></i> <?= (int)$p['views'] ?></small></td>
                    <td>
                        <form method="post" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle_active">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button class="toggle-btn <?= $p['is_active'] ? 'on' : '' ?>" type="submit">
                                <span></span>
                            </button>
                        </form>
                    </td>
                    <td class="td-actions">
                        <a href="project-edit.php?id=<?= (int)$p['id'] ?>" class="icon-btn"><i class="bi bi-pencil"></i></a>
                        <a href="../project.php?id=<?= (int)$p['id'] ?>" target="_blank" class="icon-btn"><i class="bi bi-box-arrow-up-right"></i></a>
                        <form method="post" class="inline-form" onsubmit="return confirm('Удалить проект?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button class="icon-btn icon-btn--danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="admin-empty">Проектов пока нет. <a href="project-edit.php">Создать первый</a>.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>