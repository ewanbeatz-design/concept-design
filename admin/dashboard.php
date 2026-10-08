<?php
$pageTitle = 'Дашборд';
require_once __DIR__ . '/includes/header.php';

// Сводные данные
$totalLeads       = (int)$pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$newLeads         = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE status='new'")->fetchColumn();
$contactedLeads   = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE status='contacted'")->fetchColumn();
$completedLeads   = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE status='completed'")->fetchColumn();
$totalProjects    = (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE is_active=1")->fetchColumn();

$totalViews       = (int)$pdo->query("SELECT COALESCE(SUM(views),0) FROM projects")->fetchColumn();

// Последние заявки
$recentLeads = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC LIMIT 6")->fetchAll();

// Топ проектов
$topProjects = $pdo->query("SELECT id, title, views, likes FROM projects WHERE is_active=1 ORDER BY views DESC LIMIT 5")->fetchAll();

// Заявки по дням (7 дней)
$chart = $pdo->query("
    SELECT DATE(created_at) AS day, COUNT(*) AS cnt
    FROM leads
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day
")->fetchAll();

$chartMap = [];
foreach ($chart as $row) $chartMap[$row['day']] = (int)$row['cnt'];

$days = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $days[$d] = $chartMap[$d] ?? 0;
}
$maxDay = max(1, max($days));
?>

<!-- KPI -->
<div class="kpi-grid">
    <div class="kpi-card kpi-card--accent">
        <div class="kpi-icon"><i class="bi bi-inbox"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $newLeads ?></div>
            <div class="kpi-label">Новые заявки</div>
        </div>
        <a href="leads.php?status=new" class="kpi-link">Смотреть <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon"><i class="bi bi-telephone"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $contactedLeads ?></div>
            <div class="kpi-label">В работе</div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon"><i class="bi bi-check2-circle"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $completedLeads ?></div>
            <div class="kpi-label">Завершено</div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon"><i class="bi bi-images"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $totalProjects ?></div>
            <div class="kpi-label">Активные проекты</div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon"><i class="bi bi-eye"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= number_format($totalViews, 0, '.', ' ') ?></div>
            <div class="kpi-label">Просмотры проектов</div>
        </div>
    </div>

 
</div>

<!-- CHART -->
<div class="admin-card">
    <h2 class="admin-card-title">Заявки за 7 дней</h2>
    <div class="bar-chart">
        <?php foreach ($days as $day => $cnt): ?>
        <div class="bar-wrap">
            <div class="bar-value"><?= $cnt ?></div>
            <div class="bar" style="height: <?= max(4, (int)($cnt / $maxDay * 100)) ?>%"></div>
            <div class="bar-label"><?= date('d.m', strtotime($day)) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- TWO COLUMNS -->
<div class="admin-grid-2">

    <div class="admin-card">
        <div class="admin-card-head">
            <h2 class="admin-card-title">Последние заявки</h2>
            <a href="leads.php" class="admin-link">Все <i class="bi bi-arrow-right"></i></a>
        </div>

        <?php if ($recentLeads): ?>
        <div class="leads-list">
            <?php foreach ($recentLeads as $lead): ?>
            <a href="lead.php?id=<?= (int)$lead['id'] ?>" class="lead-item">
                <div class="lead-avatar"><?= mb_strtoupper(mb_substr($lead['name'] ?: '?', 0, 1)) ?></div>
                <div class="lead-info">
                    <strong><?= h($lead['name']) ?></strong>
                    <small><?= h($lead['phone']) ?> · <?= h($lead['source']) ?></small>
                </div>
                <div class="lead-meta">
                    <span class="status-badge status-<?= h($lead['status']) ?>">
                        <?= match($lead['status']) {
                            'new' => 'Новая',
                            'contacted' => 'В работе',
                            'completed' => 'Завершена',
                            'cancelled' => 'Отменена',
                            default => $lead['status'],
                        } ?>
                    </span>
                    <small class="lead-date"><?= fmt_date($lead['created_at'], 'd.m H:i') ?></small>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p class="admin-empty">Заявок пока нет</p>
        <?php endif; ?>
    </div>

    <div class="admin-card">
        <div class="admin-card-head">
            <h2 class="admin-card-title">Топ проектов</h2>
            <a href="projects.php" class="admin-link">Все <i class="bi bi-arrow-right"></i></a>
        </div>

        <?php if ($topProjects): ?>
        <div class="top-list">
            <?php foreach ($topProjects as $i => $p): ?>
            <a href="project-edit.php?id=<?= (int)$p['id'] ?>" class="top-item">
                <span class="top-num"><?= $i + 1 ?></span>
                <span class="top-title"><?= h($p['title']) ?></span>
                <span class="top-stats">
                    <i class="bi bi-eye"></i> <?= (int)$p['views'] ?>
                    <i class="bi bi-heart"></i> <?= (int)$p['likes'] ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p class="admin-empty">Проектов пока нет</p>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>