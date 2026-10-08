<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

header('Content-Type: text/html; charset=utf-8');
echo '<pre>';

if (!$pdo) {
    die('Ошибка: нет подключения к БД');
}

/**
 * Транслит кириллицы в латиницу + URL-slug
 */
function make_slug(string $str): string {
    $str = mb_strtolower(trim($str));

    $map = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh',
        'з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o',
        'п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts',
        'ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
        '/'=>' ','\\'=>' '
    ];

    $str = strtr($str, $map);
    $str = preg_replace('/[^a-z0-9]+/', '-', $str);
    $str = trim($str, '-');

    return $str !== '' ? $str : 'item-' . time();
}

/* ============================================================
   ПРОЕКТЫ
   ============================================================ */
echo "=== ПРОЕКТЫ ===\n";

$projects = $pdo->query("SELECT id, title, slug FROM projects")->fetchAll();
$used = [];

foreach ($projects as $p) {
    $slug = !empty($p['slug']) ? $p['slug'] : make_slug($p['title']);

    $base = $slug;
    $i = 1;
    while (in_array($slug, $used, true)) {
        $slug = $base . '-' . $i++;
    }
    $used[] = $slug;

    if ($slug !== $p['slug']) {
        $pdo->prepare("UPDATE projects SET slug = ? WHERE id = ?")
            ->execute([$slug, $p['id']]);
        echo "ID {$p['id']}: {$p['title']} → {$slug}\n";
    } else {
        echo "ID {$p['id']}: уже есть slug → {$slug}\n";
    }
}

/* ============================================================
   ИНТЕРЬЕРЫ
   ============================================================ */
echo "\n=== ИНТЕРЬЕРЫ ===\n";

$interiors = $pdo->query("SELECT id, title, slug FROM interiors")->fetchAll();
$used = [];

foreach ($interiors as $item) {
    $slug = !empty($item['slug']) ? $item['slug'] : make_slug($item['title']);

    $base = $slug;
    $i = 1;
    while (in_array($slug, $used, true)) {
        $slug = $base . '-' . $i++;
    }
    $used[] = $slug;

    if ($slug !== $item['slug']) {
        $pdo->prepare("UPDATE interiors SET slug = ? WHERE id = ?")
            ->execute([$slug, $item['id']]);
        echo "ID {$item['id']}: {$item['title']} → {$slug}\n";
    } else {
        echo "ID {$item['id']}: уже есть slug → {$slug}\n";
    }
}

/* ============================================================
   КОЛЛЕКЦИИ ФАСАДОВ
   ============================================================ */
echo "\n=== КОЛЛЕКЦИИ ФАСАДОВ ===\n";

$collections = $pdo->query("SELECT id, title, subtitle, slug FROM facade_collections")->fetchAll();
$used = [];

foreach ($collections as $c) {
    $source = $c['title'] . (!empty($c['subtitle']) ? '-' . $c['subtitle'] : '');
    $slug = !empty($c['slug']) ? $c['slug'] : make_slug($source);

    $base = $slug;
    $i = 1;
    while (in_array($slug, $used, true)) {
        $slug = $base . '-' . $i++;
    }
    $used[] = $slug;

    if ($slug !== $c['slug']) {
        $pdo->prepare("UPDATE facade_collections SET slug = ? WHERE id = ?")
            ->execute([$slug, $c['id']]);
        echo "ID {$c['id']}: {$c['title']} → {$slug}\n";
    } else {
        echo "ID {$c['id']}: уже есть slug → {$slug}\n";
    }
}

echo "\n=== ГОТОВО ===\n";
echo '</pre>';