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

$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? 'all';

$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(p.title LIKE ? OR p.description LIKE ? OR p.type LIKE ? OR p.style LIKE ?)';
    $needle = '%' . $q . '%';
    array_push($params, $needle, $needle, $needle, $needle);
}
if ($status === 'active') {
    $where[] = 'p.is_active = 1';
} elseif ($status === 'archived') {
    $where[] = 'p.is_active = 0';
}

$sql = "
    SELECT p.*,
        (SELECT COUNT(*) FROM project_materials WHERE project_id = p.id) AS mat_count,
        (SELECT COUNT(*) FROM project_furniture WHERE project_id = p.id) AS furn_count,
        (SELECT COUNT(*) FROM project_features WHERE project_id = p.id) AS feature_count
    FROM projects p
";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY p.sort_order ASC, p.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

$totalProjects = (int)$pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$activeProjects = (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE is_active = 1")->fetchColumn();
$archivedProjects = $totalProjects - $activeProjects;

function project_completion(array $p): int {
    $points = 0;
    $points += !empty($p['title']) ? 1 : 0;
    $points += !empty($p['description']) ? 1 : 0;
    $points += !empty($p['image_url']) ? 1 : 0;
    $points += !empty($p['gallery_images']) && $p['gallery_images'] !== '[]' ? 1 : 0;
    $points += ((int)$p['mat_count'] > 0 || (int)$p['furn_count'] > 0 || (int)$p['feature_count'] > 0) ? 1 : 0;
    return $points * 20;
}
?>

<section class="projects-workspace">
    <div class="projects-hero">
        <div>
            <div class="projects-eyebrow">РАБОЧЕЕ ПРОСТРАНСТВО</div>
            <h2>Мои проекты</h2>
            <p>Управляйте объектами, визуалом, материалами и содержимым проекта в одном месте.</p>
        </div>
        <a href="project-edit.php" class="projects-primary">
            <i class="bi bi-plus-lg"></i> Новый проект
        </a>
    </div>

    <div class="projects-metrics">
        <div class="projects-metric">
            <div class="projects-metric-icon projects-metric-icon--terra"><i class="bi bi-grid-3x3-gap-fill"></i></div>
            <div><span>Всего проектов</span><strong><?= $totalProjects ?></strong><small><?= $totalProjects === 1 ? 'проект' : 'проектов' ?></small></div>
        </div>
        <div class="projects-metric">
            <div class="projects-metric-icon projects-metric-icon--sage"><i class="bi bi-activity"></i></div>
            <div><span>В работе</span><strong><?= $activeProjects ?></strong><small>активных объектов</small></div>
        </div>
        <div class="projects-metric">
            <div class="projects-metric-icon projects-metric-icon--sand"><i class="bi bi-archive"></i></div>
            <div><span>В архиве</span><strong><?= $archivedProjects ?></strong><small>скрытых с сайта</small></div>
        </div>
    </div>

    <div class="projects-section-head">
        <div>
            <h3>Проекты <span><?= $totalProjects ?></span></h3>
            <p>Откройте объект, чтобы перейти в его рабочее пространство.</p>
        </div>
        <form class="projects-filters" method="get">
            <label class="projects-search">
                <i class="bi bi-search"></i>
                <input name="q" value="<?= h($q) ?>" placeholder="Поиск проекта">
            </label>
            <select name="status" class="projects-filter-select" onchange="this.form.submit()">
                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Все статусы</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>В работе</option>
                <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>В архиве</option>
            </select>
        </form>
    </div>

    <?php if ($projects): ?>
    <div class="projects-grid">
        <?php foreach ($projects as $p):
            $completion = project_completion($p);
            $gallery = json_decode($p['gallery_images'] ?? '[]', true);
            $galleryCount = is_array($gallery) ? count($gallery) : 0;
        ?>
        <article class="workspace-project-card">
            <div class="workspace-project-media">
                <?php if (!empty($p['image_url'])): ?>
                    <img src="../<?= h($p['image_url']) ?>" alt="<?= h($p['title']) ?>">
                <?php else: ?>
                    <div class="workspace-project-placeholder"><i class="bi bi-image"></i></div>
                <?php endif; ?>
                <div class="workspace-project-media-overlay"></div>
                <div class="workspace-project-top">
                    <span class="workspace-status <?= $p['is_active'] ? 'workspace-status--active' : 'workspace-status--archive' ?>">
                        <i></i><?= $p['is_active'] ? 'В работе' : 'В архиве' ?>
                    </span>
                    <span class="workspace-project-id">#<?= (int)$p['id'] ?></span>
                </div>
                <div class="workspace-project-image-actions">
                    <a href="project-edit.php?id=<?= (int)$p['id'] ?>" title="Редактировать"><i class="bi bi-pencil"></i></a>
                    <a href="../project.php?id=<?= (int)$p['id'] ?>" target="_blank" title="Открыть на сайте"><i class="bi bi-arrow-up-right"></i></a>
                </div>
            </div>

            <div class="workspace-project-body">
                <div class="workspace-project-kicker"><?= h($p['type'] ?: ($p['category'] ?: 'Проект')) ?></div>
                <h4><?= h($p['title']) ?></h4>
                <?php if (!empty($p['description'])): ?>
                    <p class="workspace-project-description"><?= h(mb_substr($p['description'], 0, 120)) ?><?= mb_strlen($p['description']) > 120 ? '…' : '' ?></p>
                <?php else: ?>
                    <p class="workspace-project-description is-empty">Добавьте описание проекта</p>
                <?php endif; ?>

                <div class="workspace-project-meta">
                    <?php if (!empty($p['area'])): ?><span><i class="bi bi-rulers"></i><?= h($p['area']) ?></span><?php endif; ?>
                    <?php if (!empty($p['duration'])): ?><span><i class="bi bi-calendar3"></i><?= h($p['duration']) ?></span><?php endif; ?>
                    <span><i class="bi bi-eye"></i><?= (int)$p['views'] ?></span>
                </div>

                <div class="workspace-project-progress">
                    <div><span>Заполнено проекта</span><strong><?= $completion ?>%</strong></div>
                    <div class="workspace-progress-track"><span style="width:<?= $completion ?>%"></span></div>
                </div>

                <div class="workspace-project-stats">
                    <span><i class="bi bi-images"></i> Галерея <?= $galleryCount ?></span>
                    <span><i class="bi bi-palette"></i> Материалы <?= (int)$p['mat_count'] ?></span>
                    <span><i class="bi bi-lamp"></i> Мебель <?= (int)$p['furn_count'] ?></span>
                </div>

                <div class="workspace-project-footer">
                    <a href="project-edit.php?id=<?= (int)$p['id'] ?>" class="workspace-open">
                        Открыть проект <i class="bi bi-arrow-up-right"></i>
                    </a>
                    <form method="post" class="inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="toggle_active">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" class="workspace-footer-action" title="<?= $p['is_active'] ? 'Убрать в архив' : 'Вернуть в работу' ?>">
                            <i class="bi <?= $p['is_active'] ? 'bi-archive' : 'bi-arrow-counterclockwise' ?>"></i>
                        </button>
                    </form>
                    <form method="post" class="inline-form" onsubmit="return confirm('Удалить проект?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" class="workspace-footer-action workspace-footer-action--danger" title="Удалить">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </div>
            </div>
        </article>
        <?php endforeach; ?>

        <a href="project-edit.php" class="workspace-new-project">
            <span><i class="bi bi-plus-lg"></i></span>
            <strong>Создать новый проект</strong>
            <small>Добавьте объект и начните наполнять его контентом</small>
        </a>
    </div>
    <?php else: ?>
        <div class="workspace-project-empty">
            <div><i class="bi bi-grid-3x3-gap"></i></div>
            <strong><?= $q || $status !== 'all' ? 'Ничего не найдено' : 'Проектов пока нет' ?></strong>
            <p><?= $q || $status !== 'all' ? 'Измените поиск или фильтр.' : 'Создайте первый проект и начните собирать его рабочее пространство.' ?></p>
            <?php if (!$q && $status === 'all'): ?><a href="project-edit.php" class="projects-primary"><i class="bi bi-plus-lg"></i> Создать проект</a><?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="projects-bottom-callout">
        <i class="bi bi-stars"></i>
        <div><strong>Смета работает отдельно</strong><p>Калькулятор сметы находится в отдельной вкладке и не привязан к проектам сайта.</p></div>
        <a href="quick-estimate.php" class="workspace-text-link">Открыть смету <i class="bi bi-arrow-right"></i></a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>