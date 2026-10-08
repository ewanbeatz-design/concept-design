<?php
$pageTitle = 'Заявки';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $action = $_POST['action'] ?? '';
    $leadId = (int)($_POST['lead_id'] ?? 0);

    if ($action === 'update_status' && $leadId) {
        $status = $_POST['status'] ?? 'new';
        $allowed = ['new','contacted','completed','cancelled'];
        if (in_array($status, $allowed, true)) {
            db_update($pdo, 'leads', ['status' => $status], 'id = :id', ['id' => $leadId]);
        }
        redirect('leads.php');
    }

    if ($action === 'delete' && $leadId) {
        $pdo->prepare("DELETE FROM leads WHERE id = ?")->execute([$leadId]);
        redirect('leads.php');
    }
}

$filterStatus = $_GET['status'] ?? 'all';
$search       = trim($_GET['q'] ?? '');
$sort         = $_GET['sort'] ?? 'date_desc';

$where  = [];
$params = [];

if ($filterStatus !== 'all' && in_array($filterStatus, ['new','contacted','completed','cancelled'], true)) {
    $where[]  = "status = ?";
    $params[] = $filterStatus;
}

if ($search !== '') {
    $where[] = "(name LIKE ? OR phone LIKE ? OR email LIKE ? OR source LIKE ?)";
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like);
}

$sql = "SELECT * FROM leads";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);

switch ($sort) {
    case 'date_asc': $sql .= " ORDER BY created_at ASC"; break;
    case 'name':     $sql .= " ORDER BY name ASC"; break;
    case 'status':   $sql .= " ORDER BY FIELD(status,'new','contacted','completed','cancelled')"; break;
    default:         $sql .= " ORDER BY created_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll();

$kanban = ['new' => [], 'contacted' => [], 'completed' => [], 'cancelled' => []];
foreach ($leads as $lead) {
    if (isset($kanban[$lead['status']])) $kanban[$lead['status']][] = $lead;
}

$statuses = [
    'new'       => ['label' => 'Новые',     'color' => '#c79d75'],
    'contacted' => ['label' => 'В работе',  'color' => '#4a8fd8'],
    'completed' => ['label' => 'Завершены', 'color' => '#4a9d6a'],
    'cancelled' => ['label' => 'Отменены',  'color' => '#9a9a9a'],
];

$view = $_GET['view'] ?? 'kanban';
?>

<div class="filters-bar">
    <form method="get" class="filters-form">
        <input type="hidden" name="view" value="<?= h($view) ?>">

        <div class="filter-search">
            <i class="bi bi-search"></i>
            <input type="text" name="q" placeholder="Поиск: имя, телефон, источник" value="<?= h($search) ?>">
        </div>

        <select name="status" onchange="this.form.submit()">
            <option value="all">Все статусы</option>
            <?php foreach ($statuses as $key => $s): ?>
                <option value="<?= $key ?>" <?= $filterStatus === $key ? 'selected' : '' ?>><?= $s['label'] ?></option>
            <?php endforeach; ?>
        </select>

        <select name="sort" onchange="this.form.submit()">
            <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Сначала новые</option>
            <option value="date_asc"  <?= $sort === 'date_asc'  ? 'selected' : '' ?>>Сначала старые</option>
            <option value="name"      <?= $sort === 'name'      ? 'selected' : '' ?>>По имени</option>
            <option value="status"    <?= $sort === 'status'    ? 'selected' : '' ?>>По статусу</option>
        </select>

        <button type="submit" class="admin-btn admin-btn--primary">
            <i class="bi bi-funnel"></i> Применить
        </button>
    </form>

    <div class="view-switch">
        <a href="?view=kanban&<?= http_build_query(['status'=>$filterStatus,'q'=>$search,'sort'=>$sort]) ?>"
           class="view-btn <?= $view === 'kanban' ? 'active' : '' ?>">
            <i class="bi bi-kanban"></i> Канбан
        </a>
        <a href="?view=list&<?= http_build_query(['status'=>$filterStatus,'q'=>$search,'sort'=>$sort]) ?>"
           class="view-btn <?= $view === 'list' ? 'active' : '' ?>">
            <i class="bi bi-list-ul"></i> Таблица
        </a>
    </div>
</div>

<?php if ($view === 'kanban'): ?>
<div class="kanban">
    <?php foreach ($statuses as $key => $s): ?>
    <div class="kanban-col" data-status="<?= $key ?>">
        <div class="kanban-head">
            <span class="kanban-dot" style="background: <?= $s['color'] ?>"></span>
            <span class="kanban-title"><?= $s['label'] ?></span>
            <span class="kanban-count"><?= count($kanban[$key]) ?></span>
        </div>

        <div class="kanban-body" data-status="<?= $key ?>">
            <?php foreach ($kanban[$key] as $lead): ?>
            <div class="kanban-card" data-id="<?= (int)$lead['id'] ?>">
                <div class="kanban-card-head">
                    <strong><?= h($lead['name']) ?></strong>
                    <div class="kanban-card-actions">
                        <a href="lead.php?id=<?= (int)$lead['id'] ?>" class="icon-btn" title="Открыть">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                </div>
                <a href="tel:<?= h(preg_replace('/\D/', '', $lead['phone'])) ?>" class="kanban-phone">
                    <i class="bi bi-telephone"></i> <?= h($lead['phone']) ?>
                </a>
                <?php if (!empty($lead['source'])): ?>
                <div class="kanban-source"><?= h($lead['source']) ?></div>
                <?php endif; ?>
                <div class="kanban-footer">
                    <span><?= fmt_date($lead['created_at'], 'd.m H:i') ?></span>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (!$kanban[$key]): ?>
                <div class="kanban-empty">Пусто</div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($view === 'list'): ?>
<div class="admin-card">
    <?php if ($leads): ?>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Имя</th>
                    <th>Телефон</th>
                    <th>Источник</th>
                    <th>Статус</th>
                    <th>Дата</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads as $lead): ?>
                <tr>
                    <td class="td-mono"><?= (int)$lead['id'] ?></td>
                    <td><strong><?= h($lead['name']) ?></strong></td>
                    <td><a href="tel:<?= h(preg_replace('/\D/', '', $lead['phone'])) ?>"><?= h($lead['phone']) ?></a></td>
                    <td><small><?= h($lead['source'] ?: '—') ?></small></td>
                    <td>
                        <form method="post" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="lead_id" value="<?= (int)$lead['id'] ?>">
                            <select name="status" class="status-select status-<?= h($lead['status']) ?>" onchange="this.form.submit()">
                                <?php foreach ($statuses as $key => $s): ?>
                                    <option value="<?= $key ?>" <?= $lead['status'] === $key ? 'selected' : '' ?>><?= $s['label'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td class="td-mono"><?= fmt_date($lead['created_at'], 'd.m.Y H:i') ?></td>
                    <td class="td-actions">
                        <a href="lead.php?id=<?= (int)$lead['id'] ?>" class="icon-btn" title="Открыть"><i class="bi bi-pencil"></i></a>
                        <form method="post" class="inline-form" onsubmit="return confirm('Удалить заявку?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="lead_id" value="<?= (int)$lead['id'] ?>">
                            <button class="icon-btn icon-btn--danger" title="Удалить"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="admin-empty">Ничего не найдено</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>