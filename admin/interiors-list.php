<?php
$pageTitle = 'Интерьеры';
require_once __DIR__ . '/includes/header.php';

$interiors = $pdo->query("
    SELECT * FROM interiors
    ORDER BY sort_order ASC, created_at DESC
")->fetchAll();
?>

<div class="admin-page-head">
    <div></div>
    <a href="interiors-edit.php" class="admin-btn admin-btn--primary">
        <i class="bi bi-plus-lg"></i> Добавить интерьер
    </a>
</div>

<div class="admin-card">
    <?php if ($interiors): ?>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Превью</th>
                    <th>Название</th>
                    <th>Тип</th>
                    <th>Площадь</th>
                    <th>Порядок</th>
                    <th>Активен</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($interiors as $item): ?>
                <tr data-id="<?= (int)$item['id'] ?>">
                    <td class="td-mono"><?= (int)$item['id'] ?></td>
                    <td>
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="../<?= h($item['image_url']) ?>" alt="" class="td-thumb">
                        <?php else: ?>
                            <div class="td-thumb td-thumb--empty"><i class="bi bi-image"></i></div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= h($item['title']) ?></strong></td>
                    <td><small><?= h($item['type'] ?: '—') ?></small></td>
                    <td><small><?= h($item['area'] ?: '—') ?></small></td>
                    <td class="td-mono"><?= (int)$item['sort_order'] ?></td>
                    <td>
                        <button class="toggle-btn js-interior-toggle <?= $item['is_active'] ? 'on' : '' ?>" data-id="<?= (int)$item['id'] ?>">
                            <span></span>
                        </button>
                    </td>
                    <td class="td-actions">
                        <a href="interiors-edit.php?id=<?= (int)$item['id'] ?>" class="icon-btn"><i class="bi bi-pencil"></i></a>
                        <button class="icon-btn icon-btn--danger js-interior-delete" data-id="<?= (int)$item['id'] ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="admin-empty">Интерьеров пока нет. <a href="interiors-edit.php">Добавить первый</a>.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>