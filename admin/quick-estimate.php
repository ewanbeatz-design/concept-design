<?php
$pageTitle='Смета';
require_once __DIR__ . '/includes/header.php';

if (!$pdo) {
    echo '<div class="admin-card"><h2 class="admin-card-title">Нет подключения к БД</h2></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pdo->exec("CREATE TABLE IF NOT EXISTS quick_estimate_catalog(
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
category VARCHAR(40) NOT NULL,
name VARCHAR(255) NOT NULL,
brand VARCHAR(100) NULL,
article VARCHAR(120) NULL,
unit VARCHAR(20) NOT NULL DEFAULT 'шт',
price DECIMAL(12,2) NOT NULL DEFAULT 0,
active TINYINT(1) NOT NULL DEFAULT 1,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
KEY category(category),KEY active(active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$seed=[
['materials','ЛДСП 16 мм 2800×2070 — Белый','Kronospan','101 PE','лист',1830],
['materials','ЛДСП 16 мм 2800×2070 — Белый фасадный','Kronospan','101 PR','лист',2319],
['materials','ЛДСП 16 мм 2800×2070 — Антрацит','Kronospan','0164 PE','лист',2619],
['materials','ЛДСП 16 мм 2800×2070 — Бежевый','Kronospan','0522 PE','лист',2619],
['materials','ЛДСП 16 мм 2800×2070 — Белый Бриллиант','Kronospan','8681 GL','лист',3448],
['materials','ЛДСП 16 мм 2800×2070 — Бежевый песок','EGGER','U156 ST9','лист',5414],
['materials','ЛДСП 16 мм 2800×2070 — Кубанит серый','EGGER','U767 ST9','лист',5579],
['materials','ЛДСП 16 мм 2800×2070 — Дуб Ровато','EGGER','H3322 ST17','лист',6821],
['materials','ЛМДФ 16 мм — Антрацит','Kronospan','164 SU','м²',563],
['materials','Кромка ABS 19×0,4 мм','EGGER','U156 ST9','м',17],
['materials','Кромка ABS 19×2 мм','EGGER','типовая','м',55],
['materials','ДВП 3,2 мм белая','—','—','м²',180],
['countertop','Столешница ДСП 38 мм 3000×600','Россия','разные декоры','шт',6757],
['countertop','Столешница EGGER 38 мм 4100×600','EGGER','разные декоры','шт',10500],
['countertop','Столешница EGGER 38 мм 4100×600 — мрамор','EGGER','F800 ST9','шт',10850],
['countertop','Столешница EGGER 38 мм 4100×600 — дуб','EGGER','H1145 ST10','шт',9710],
['countertop','Столешница EGGER 38 мм 4100×920 — дуб','EGGER','H3157 STG2','шт',18680],
['hardware','Петля CLIP top 110° без BLUMOTION','BLUM','71T3750','компл.',294],
['hardware','Петля CLIP top BLUMOTION 110°','BLUM','типовая','компл.',650],
['hardware','Петля Sensys 110° с ответной планкой','HETTICH','D0','компл.',588.50],
['hardware','Направляющие TANDEM 550 мм 30 кг','BLUM','550H2700.03','компл.',1371],
['hardware','Направляющие TANDEM BLUMOTION 550 мм','BLUM','550H2700B','компл.',1833],
['hardware','TANDEM plus BLUMOTION 550 мм','BLUM','560H2500B','компл.',3745],
['hardware','LEGRABOX pure M 550 мм','BLUM','770M5502S','компл.',2918],
['hardware','LEGRABOX pure C 550 мм','BLUM','770C5502S','компл.',4428],
['hardware','LEGRABOX pure K 550 мм','BLUM','770K5502S','компл.',4612],
['hardware','Ручка мебельная 160 мм','GTV','типовая','шт',350],
['hardware','Профиль GOLA горизонтальный','GOLA','типовой','м',1450],
['hardware','Опора регулируемая 100 мм','—','—','шт',45],
['hardware','Цоколь алюминиевый 100 мм','—','—','м',650],
['fasteners','Конфирмат 7×50','—','—','шт',8],
['fasteners','Евровинт 6,3×13','—','—','шт',6],
['fasteners','Саморез 3,5×16','—','—','шт',3.5],
['fasteners','Саморез 4×30','—','—','шт',4],
['fasteners','Шкант 8×30','—','—','шт',5],
['fasteners','Стяжка Minifix','—','—','компл.',35],
['fasteners','Крепление задней стенки','—','—','шт',12],
['fasteners','Клей ПВА D3','Titebond','или аналог','кг',650],
['fasteners','Силикон нейтральный','—','—','шт',650]
];
if (!(int)$pdo->query("SELECT COUNT(*) FROM quick_estimate_catalog")->fetchColumn()) {
    $s=$pdo->prepare("INSERT INTO quick_estimate_catalog(category,name,brand,article,unit,price) VALUES(?,?,?,?,?,?)");
    foreach($seed as $x)$s->execute($x);
}
$cat=$pdo->query("SELECT * FROM quick_estimate_catalog WHERE active=1 ORDER BY FIELD(category,'materials','countertop','hardware','fasteners'),name")->fetchAll();
$labels=['materials'=>'ЛДСП / МДФ / Кромка','countertop'=>'Столешницы','hardware'=>'Фурнитура','fasteners'=>'Крепёж и расходники'];
function qem($n){return number_format((float)$n,2,',',' ').' ₽';}
?>

<div class="sm-estimate-page">
    <div class="sm-estimate-heading">
        <div>
            <div class="sm-eyebrow">CONCEPT / СМЕТА</div>
            <div class="sm-title-row">
                <div class="sm-title-icon"><i class="bi bi-calculator"></i></div>
                <div>
                    <h1>Смета</h1>
                    <p>Самостоятельный калькулятор расчёта — не привязан к проектам сайта.</p>
                </div>
            </div>
        </div>
        <div class="sm-heading-actions">
            <button class="sm-outline" type="button" id="smSave"><i class="bi bi-save2"></i> Сохранить</button>
            <button class="sm-outline" type="button" id="smPrint"><i class="bi bi-file-earmark-arrow-down"></i> Экспорт</button>
            <button class="sm-primary" type="button" data-bs-toggle="modal" data-bs-target="#smTemplateModal"><i class="bi bi-grid-3x3-gap"></i> Готовая смета</button>
        </div>
    </div>

    <div class="sm-summary">
        <div class="sm-summary-total">
            <span>ИТОГО ПО СМЕТЕ</span>
            <strong id="smGrand">0 ₽</strong>
            <small>включая НДС 20%</small>
        </div>
        <div class="sm-summary-stats">
            <div><b id="smSectionsCount">0</b><span>разделов</span></div>
            <div><b id="smItemsCount">0</b><span>позиций</span></div>
            <div><b id="smDirectShort">0 ₽</b><span>прямые затраты</span></div>
        </div>
        <button class="sm-summary-more" type="button" id="smClear"><i class="bi bi-arrow-counterclockwise"></i> Очистить</button>
    </div>

    <div class="sm-calc-controls">
        <div>
            <span class="sm-control-label">МЕТОД РАСЧЁТА</span>
            <select id="smMethod">
                <option value="resource">Ресурсный</option>
                <option value="base-index">Базисно-индексный</option>
                <option value="resource-index">Ресурсно-индексный</option>
            </select>
        </div>
        <label class="sm-check"><input id="smWinter" type="checkbox"> Зимнее удорожание <b>+12%</b></label>
        <label class="sm-check"><input id="smTight" type="checkbox"> Стеснённые условия <b>+8%</b></label>
        <label class="sm-custom">Свой коэффициент <input id="smCustom" type="number" value="1" min=".1" max="5" step=".01"></label>
    </div>

    <div class="sm-toolbar">
        <div class="sm-toolbar-tabs">
            <button class="active" type="button">Смета</button>
            <button type="button" class="disabled">График <span>скоро</span></button>
            <button type="button" class="disabled">Документы <span>скоро</span></button>
        </div>
        <div class="sm-toolbar-actions">
            <button type="button" id="smAddSection"><i class="bi bi-plus-lg"></i> Добавить раздел</button>
            <button type="button" data-bs-toggle="modal" data-bs-target="#smCatalogModal"><i class="bi bi-book"></i> Каталог</button>
        </div>
    </div>

    <div id="smEstimateList" class="sm-estimate-list">
        <div class="sm-empty">
            <i class="bi bi-calculator"></i>
            <h3>Смета пока пустая</h3>
            <p>Создайте раздел и добавьте работы или материалы из каталога.</p>
            <button class="sm-primary" type="button" id="smEmptyAdd"><i class="bi bi-plus-lg"></i> Создать раздел</button>
        </div>
    </div>

    <div class="sm-bottom">
        <div class="sm-totals">
            <div><span>Прямые затраты</span><b id="smDirect">0 ₽</b></div>
            <div><span>Накладные расходы (НР) · 15%</span><b id="smOverhead">0 ₽</b></div>
            <div><span>Сметная прибыль (СП) · 8%</span><b id="smProfit">0 ₽</b></div>
            <div><span>Коэффициенты</span><b id="smCoeff">× 1.000</b></div>
            <div><span>НДС · 20%</span><b id="smVat">0 ₽</b></div>
            <div class="sm-grand"><span>Итого в текущем уровне цен</span><strong id="smTotal">0 ₽</strong></div>
        </div>
    </div>
</div>

<div class="modal fade" id="smCatalogModal" tabindex="-1">
 <div class="modal-dialog modal-dialog-centered modal-xl">
  <div class="modal-content sm-modal">
   <div class="modal-header">
    <div><div class="sm-eyebrow">КАТАЛОГ</div><h5 class="modal-title">Добавить работу или материал</h5><small>Выберите позицию и добавьте её в нужный раздел.</small></div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
   </div>
   <div class="modal-body">
    <div class="sm-catalog-search"><i class="bi bi-search"></i><input id="smCatalogSearch" placeholder="Поиск материала, артикула, бренда..."></div>
    <div class="sm-catalog-grid" id="smCatalogGrid">
    <?php foreach($cat as $x): ?>
      <button type="button" class="sm-catalog-item" data-id="<?=$x['id']?>" data-name="<?=h(mb_strtolower($x['name'].' '.$x['brand'].' '.$x['article']))?>" data-title="<?=h($x['name'])?>" data-brand="<?=h($x['brand'])?>" data-unit="<?=h($x['unit'])?>" data-price="<?=$x['price']?>">
       <span class="sm-cat-icon"><i class="bi bi-tools"></i></span>
       <span class="sm-cat-info"><strong><?=h($x['name'])?></strong><small><?=h($labels[$x['category']])?> · <?=h($x['brand'])?> · <?=h($x['article'])?></small></span>
       <strong><?=qem($x['price'])?> / <?=h($x['unit'])?></strong>
       <span class="sm-cat-check"><i class="bi bi-check-lg"></i></span>
      </button>
    <?php endforeach; ?>
    </div>
    <div class="sm-selected" id="smSelected" hidden>
      <span>Выбрано: <b id="smSelectedName"></b></span>
      <label>Количество <input id="smSelectedQty" type="number" value="1" min=".001" step=".001"></label>
    </div>
   </div>
   <div class="modal-footer">
    <button class="sm-outline" type="button" data-bs-dismiss="modal">Отмена</button>
    <button class="sm-primary" type="button" id="smCatalogAdd" disabled><i class="bi bi-plus-lg"></i> Добавить в смету</button>
   </div>
  </div>
 </div>
</div>

<div class="modal fade" id="smTemplateModal" tabindex="-1">
 <div class="modal-dialog modal-dialog-centered modal-xl">
  <div class="modal-content sm-modal">
   <div class="modal-header">
    <div><div class="sm-eyebrow">ШАБЛОНЫ</div><h5 class="modal-title">Готовые шаблоны смет</h5><small>Выберите основу — позиции автоматически разложатся по разделам.</small></div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
   </div>
   <div class="modal-body">
    <div class="sm-template-grid" id="smTemplates">
      <button type="button" class="sm-template" data-template="repair"><span class="sm-template-icon"><i class="bi bi-house-gear"></i></span><strong>Косметический ремонт</strong><small>Подготовка · Черновые · Чистовая · Финиш</small><b>11 позиций</b></button>
      <button type="button" class="sm-template" data-template="capital"><span class="sm-template-icon"><i class="bi bi-buildings"></i></span><strong>Капитальный ремонт</strong><small>Демонтаж · Черновые · Электрика · Сантехника · Отделка</small><b>20 позиций</b></button>
      <button type="button" class="sm-template" data-template="bath"><span class="sm-template-icon"><i class="bi bi-droplet-half"></i></span><strong>Ремонт санузла</strong><small>Демонтаж · Гидроизоляция · Сантехника · Плитка</small><b>15 позиций</b></button>
      <button type="button" class="sm-template" data-template="electric"><span class="sm-template-icon"><i class="bi bi-lightning-charge"></i></span><strong>Электромонтаж</strong><small>Разметка · Кабель · Щит · Чистовая электрика</small><b>11 позиций</b></button>
      <button type="button" class="sm-template" data-template="wardrobe"><span class="sm-template-icon"><i class="bi bi-door-closed"></i></span><strong>Шкаф-купе</strong><small>Корпус · Фасады · Наполнение · Фурнитура</small><b>12 позиций</b></button>
      <button type="button" class="sm-template" data-template="kitchen"><span class="sm-template-icon"><i class="bi bi-layout-text-sidebar-reverse"></i></span><strong>Кухня</strong><small>Корпус · Фасады · Столешница · Фурнитура</small><b>16 позиций</b></button>
      <button type="button" class="sm-template" data-template="wardrobe2"><span class="sm-template-icon"><i class="bi bi-grid-3x3-gap"></i></span><strong>Распашной шкаф</strong><small>Корпус · Двери · Полки · Петли · Ручки</small><b>11 позиций</b></button>
      <button type="button" class="sm-template" data-template="vanity"><span class="sm-template-icon"><i class="bi bi-droplet"></i></span><strong>Тумба под раковину</strong><small>Корпус · Фасады · Столешница · Фурнитура</small><b>9 позиций</b></button>
    </div>
   </div>
   <div class="modal-footer"><button class="sm-outline" type="button" data-bs-dismiss="modal">Отмена</button></div>
  </div>
 </div>
</div>

<style>
.sm-estimate-page{max-width:1580px;margin:0 auto}.sm-estimate-heading{display:flex;justify-content:space-between;align-items:flex-end;gap:24px;margin-bottom:24px}.sm-eyebrow{font-size:9px;letter-spacing:.16em;color:var(--gold-2);font-weight:800;text-transform:uppercase}.sm-title-row{display:flex;align-items:center;gap:13px}.sm-title-icon{width:48px;height:48px;border-radius:14px;background:rgba(199,157,109,.12);color:var(--gold-2);display:grid;place-items:center;font-size:21px}.sm-title-row h1{font-size:29px;margin:4px 0;color:var(--ink)}.sm-title-row p{margin:0;color:var(--muted);font-size:11px}.sm-heading-actions{display:flex;gap:8px;align-items:center}.sm-outline,.sm-primary{display:inline-flex;align-items:center;justify-content:center;gap:7px;border-radius:9px;padding:9px 13px;font:700 11px inherit;cursor:pointer}.sm-outline{border:1px solid var(--line);background:transparent;color:var(--ink)}.sm-outline:hover{background:var(--bg-3)}.sm-primary{border:1px solid var(--ink);background:var(--ink);color:var(--bg)}.sm-primary:hover{opacity:.9}.sm-summary{background:linear-gradient(135deg,#24231f,#34312a);border:1px solid rgba(199,157,109,.22);border-radius:var(--r);padding:19px 22px;color:#fff;display:flex;align-items:center;gap:28px;margin-bottom:20px}.sm-summary-total{min-width:250px}.sm-summary-total span,.sm-summary-total small{display:block;color:#c8bcae;font-size:9px;letter-spacing:.08em}.sm-summary-total strong{display:block;font:600 28px monospace;margin:6px 0 3px}.sm-summary-stats{display:flex;gap:24px;padding-left:28px;border-left:1px solid rgba(199,157,109,.3)}.sm-summary-stats div{display:grid;gap:2px}.sm-summary-stats b{font:600 14px monospace;color:#fff}.sm-summary-stats span{font-size:9px;color:#c8bcae}.sm-summary-more{margin-left:auto;border:1px solid rgba(255,255,255,.16);background:transparent;color:#fff;border-radius:8px;padding:8px 11px;font-size:10px}.sm-calc-controls{display:flex;align-items:center;gap:16px;flex-wrap:wrap;padding:12px 3px;border-bottom:1px solid var(--line)}.sm-calc-controls>div{display:grid;gap:4px}.sm-control-label{font-size:8px;letter-spacing:.1em;color:var(--muted);font-weight:800}.sm-calc-controls select,.sm-custom input{border:1px solid var(--line);background:var(--bg-2);color:var(--ink);border-radius:7px;padding:7px 9px;font-size:10px}.sm-check{display:flex;align-items:center;gap:6px;color:var(--muted);font-size:10px}.sm-check b{color:var(--gold-2)}.sm-check input{accent-color:var(--gold-2)}.sm-custom{display:flex;align-items:center;gap:7px;font-size:10px;color:var(--muted);margin-left:auto}.sm-custom input{width:70px;text-align:right}.sm-toolbar{display:flex;justify-content:space-between;align-items:center;padding:16px 3px;border-bottom:1px solid var(--line)}.sm-toolbar-tabs,.sm-toolbar-actions{display:flex;align-items:center;gap:6px}.sm-toolbar-tabs button,.sm-toolbar-actions button{border:0;background:transparent;color:var(--muted);font-size:10px;padding:8px 10px;border-radius:7px}.sm-toolbar-tabs button.active{background:var(--bg-3);color:var(--ink);font-weight:800}.sm-toolbar-tabs button.disabled{opacity:.45}.sm-toolbar-tabs span{font-size:8px}.sm-toolbar-actions button{color:var(--ink);font-weight:700}.sm-toolbar-actions button:hover{background:var(--bg-3)}.sm-estimate-list{padding-top:18px}.sm-group{margin-bottom:22px}.sm-group-head{display:grid;grid-template-columns:30px minmax(0,1fr) auto auto 32px;gap:10px;align-items:center;padding:0 9px 10px}.sm-group-number{font:500 10px monospace;color:var(--gold-2)}.sm-group-head h3{margin:0;font-size:13px}.sm-group-head small{font-size:9px;color:var(--muted)}.sm-group-head strong{font:600 11px monospace}.sm-group-menu{border:0;background:transparent;color:var(--muted);font-size:16px}.sm-table{border:1px solid var(--line);border-radius:12px;overflow:hidden;background:var(--bg-2)}.sm-table-head,.sm-row{display:grid;grid-template-columns:minmax(210px,2fr) 110px 75px 125px 135px 80px;gap:12px;align-items:center;padding:0 15px}.sm-table-head{height:34px;background:var(--bg-3);color:var(--muted-2);font:600 8px monospace}.sm-row{min-height:54px;border-top:1px solid var(--line);font-size:11px;color:var(--muted);cursor:pointer}.sm-row:hover{background:rgba(199,157,109,.035)}.sm-work{display:flex;align-items:center;gap:8px;color:var(--ink);font-weight:700}.sm-dot{width:5px;height:5px;border-radius:50%;background:var(--muted-2);flex:0 0 auto}.sm-cell-input{width:100%;border:1px solid var(--line-2);background:var(--bg-3);color:var(--ink);border-radius:6px;padding:7px;font-size:11px}.sm-actions{display:flex;justify-content:flex-end;gap:2px}.sm-actions button{border:0;background:transparent;color:var(--muted);padding:5px}.sm-actions button:hover{color:var(--gold-2)}.sm-add-row{display:flex;gap:8px;align-items:center;border:0;background:transparent;color:var(--gold-2);padding:13px 15px;font-size:10px;font-weight:800}.sm-add-row.secondary{color:var(--muted);padding-top:0}.sm-bottom{display:flex;justify-content:flex-end;margin-top:4px}.sm-totals{width:min(690px,100%);border-top:2px solid var(--line-2);padding-top:7px}.sm-totals>div{display:flex;justify-content:space-between;gap:20px;padding:9px 8px;color:var(--muted);font-size:11px}.sm-totals b{font:600 12px monospace;color:var(--ink)}.sm-grand{border-top:1px solid var(--line-2);margin-top:5px;padding-top:16px!important;color:var(--ink)!important}.sm-grand strong{font:700 22px monospace;color:var(--gold-2)}.sm-empty{border:1px dashed var(--line-2);border-radius:14px;min-height:280px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:7px;color:var(--muted)}.sm-empty i{font-size:34px;color:var(--gold-2)}.sm-empty h3{font-size:15px;color:var(--ink);margin:6px 0 0}.sm-empty p{font-size:11px;margin:0 0 10px}.sm-modal{border:1px solid var(--line);background:var(--bg-2);color:var(--ink)}.sm-modal .modal-header,.sm-modal .modal-footer{border-color:var(--line)}.sm-modal .modal-title{margin:4px 0 3px;font-size:18px}.sm-modal .modal-header small{font-size:10px;color:var(--muted)}.sm-catalog-search{display:flex;align-items:center;gap:9px;border:1px solid var(--line);background:var(--bg-3);border-radius:10px;padding:0 12px;margin-bottom:14px}.sm-catalog-search input{width:100%;border:0;outline:0;background:transparent;color:var(--ink);padding:11px}.sm-catalog-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;max-height:58vh;overflow:auto}.sm-catalog-item{display:grid;grid-template-columns:34px minmax(0,1fr) auto 20px;gap:9px;align-items:center;text-align:left;border:1px solid var(--line);background:var(--bg-3);color:var(--ink);border-radius:10px;padding:10px}.sm-catalog-item:hover,.sm-catalog-item.selected{border-color:var(--gold);background:rgba(199,157,109,.07)}.sm-cat-icon{width:30px;height:30px;border-radius:8px;background:rgba(199,157,109,.1);display:grid;place-items:center;color:var(--gold-2)}.sm-cat-info{min-width:0}.sm-cat-info strong,.sm-cat-info small{display:block}.sm-cat-info strong{font-size:10px}.sm-cat-info small{font-size:8px;color:var(--muted);margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.sm-catalog-item>strong{font:600 10px monospace;white-space:nowrap}.sm-cat-check{opacity:0;color:var(--gold-2)}.sm-catalog-item.selected .sm-cat-check{opacity:1}.sm-selected{display:flex;justify-content:space-between;align-items:center;border:1px solid var(--line);border-radius:9px;padding:11px;margin-top:12px;font-size:10px}.sm-selected label{display:flex;align-items:center;gap:7px;color:var(--muted)}.sm-selected input{width:75px;border:1px solid var(--line);background:var(--bg-3);color:var(--ink);border-radius:7px;padding:6px;text-align:right}.sm-template-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}.sm-template{display:grid;grid-template-columns:42px 1fr auto;grid-template-rows:auto auto auto;column-gap:10px;text-align:left;border:1px solid var(--line);background:var(--bg-3);border-radius:12px;padding:14px;color:var(--ink)}.sm-template:hover{border-color:var(--gold)}.sm-template-icon{grid-row:1/4;width:42px;height:42px;border-radius:11px;background:rgba(199,157,109,.11);display:grid;place-items:center;color:var(--gold-2);font-size:18px}.sm-template strong{font-size:12px}.sm-template small{font-size:9px;color:var(--muted);margin-top:4px}.sm-template b{font:600 9px monospace;color:var(--gold-2);margin-top:6px}.sm-row.editing{cursor:default}.sm-row.editing .sm-actions button{display:inline-flex}.sm-measure{font-size:9px;color:var(--gold-2);margin-left:3px}@media(max-width:1050px){.sm-summary{flex-wrap:wrap}.sm-summary-more{margin-left:0}.sm-calc-controls{align-items:flex-start}.sm-custom{margin-left:0}.sm-heading-actions{flex-wrap:wrap}.sm-catalog-grid{grid-template-columns:1fr}}@media(max-width:800px){.sm-estimate-heading{flex-direction:column;align-items:flex-start}.sm-heading-actions{width:100%}.sm-heading-actions>*{flex:1}.sm-summary{display:grid;grid-template-columns:1fr}.sm-summary-stats{border-left:0;border-top:1px solid rgba(199,157,109,.3);padding:14px 0 0}.sm-toolbar{align-items:flex-start;gap:10px;flex-direction:column}.sm-table{overflow-x:auto}.sm-table-head,.sm-row{min-width:760px}.sm-template-grid{grid-template-columns:1fr}}@media(max-width:520px){.sm-summary-stats{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}.sm-title-row h1{font-size:23px}.sm-calc-controls{gap:10px}.sm-check,.sm-custom{width:100%}.sm-heading-actions>*{flex:none}.sm-catalog-item{grid-template-columns:30px minmax(0,1fr) 18px}.sm-catalog-item>strong{display:none}}
@media print{.admin-sidebar,.admin-topbar,.sm-estimate-heading,.sm-calc-controls,.sm-toolbar,.sm-summary-more,.sm-actions,.sm-add-row,.modal{display:none!important}.admin-shell{display:block}.admin-content{padding:0}.sm-estimate-page{max-width:none}.sm-summary{margin-bottom:15px}.sm-table{border:1px solid #bbb}.sm-table-head,.sm-row{min-width:0}.sm-grand strong{color:#000}}
</style>

<script>
(()=> {
const KEY='concept_standalone_estimate_v2';
const state={sections:[],winter:false,tight:false,custom:1,method:'resource'};
const templates={
 repair:[['Подготовка и демонтаж',[['Демонтаж обоев','м²',120],['Демонтаж напольного покрытия','м²',150],['Демонтаж плинтуса','м.п.',69]]],['Черновые работы',[['Грунтовка стен','м²',80],['Шпаклевка стен','м²',420],['Подготовка пола','м²',180]]],['Чистовая отделка',[['Покраска стен в два слоя','м²',350],['Укладка ламината','м²',450],['Монтаж плинтуса','м.п.',180]]],['Электрика',[['Монтаж розетки','шт',650]]],['Финишные работы',[['Уборка после ремонта','м²',120]]]],
 capital:[['Демонтаж',[['Демонтаж плитки','м²',350],['Демонтаж стяжки до 5 см','м²',650],['Демонтаж перегородки','м²',850]]],['Черновые работы',[['Грунтовка стен','м²',80],['Штукатурка стен по маякам','м²',650],['Шпаклевка стен','м²',420],['Стяжка пола до 50 мм','м²',900],['Гидроизоляция пола','м²',450]]],['Электрика',[['Прокладка кабеля','м.п.',120],['Монтаж подрозетника','шт',250],['Монтаж розетки','шт',650]]],['Сантехника',[['Разводка водоснабжения','точка',1800],['Разводка канализации','точка',1600],['Монтаж инсталляции','шт',4500]]],['Чистовая отделка',[['Укладка керамогранита','м²',1400],['Покраска стен в два слоя','м²',350],['Укладка ламината','м²',450],['Монтаж межкомнатной двери','шт',4500]]],['Финишные работы',[['Монтаж плинтуса','м.п.',180],['Финальная уборка','м²',120]]]],
 bath:[['Демонтаж',[['Демонтаж плитки','м²',350],['Демонтаж сантехники','шт',1200]]],['Черновые работы',[['Гидроизоляция пола','м²',450],['Выравнивание стен','м²',650],['Стяжка пола','м²',900]]],['Сантехника',[['Разводка водоснабжения','точка',1800],['Разводка канализации','точка',1600],['Монтаж коллектора','шт',3500],['Монтаж инсталляции','шт',4500],['Монтаж смесителя','шт',1200]]],['Плитка',[['Укладка керамогранита','м²',1400],['Затирка швов','м²',300]]],['Электрика',[['Монтаж розетки','шт',650],['Монтаж светильника','шт',1200]]]],
 electric:[['Подготовка',[['Разметка трасс','м.п.',120]]],['Черновая электрика',[['Штробление стен','м.п.',350],['Установка подрозетника','шт',250],['Прокладка кабеля','м.п.',120],['Сборка электрощита','шт',8500],['Монтаж распределительной коробки','шт',450]]],['Чистовая электрика',[['Монтаж розетки','шт',650],['Монтаж выключателя','шт',650],['Монтаж светильника','шт',1200],['Подключение бытового оборудования','шт',900]]],['Пусконаладка',[['Проверка линий и автоматики','компл.',2500]]]],
 wardrobe:[['Корпус',[['ЛДСП 16 мм — корпус','лист',1830],['Кромка ABS 19×0,4 мм','м',17],['Задняя стенка ДВП','м²',180],['Сборка корпуса шкафа','шт',4500]]],['Фасады',[['Фасад МДФ','м²',4200],['Кромка ABS 19×2 мм','м',55],['Ручка мебельная 160 мм','шт',350]]],['Наполнение',[['Штанга для одежды','м',650],['Полка ЛДСП','шт',750],['Ящик с направляющими','компл.',2800]]],['Фурнитура',[['Петля CLIP top 110°','компл.',294],['Направляющие TANDEM 550 мм','компл.',1371]]]],
 kitchen:[['Корпус',[['ЛДСП 16 мм — корпус','лист',1830],['Кромка ABS 19×0,4 мм','м',17],['Задняя стенка ДВП','м²',180],['Сборка кухонного корпуса','м.п.',3500]]],['Фасады',[['Фасад МДФ крашеный','м²',6800],['Кромка ABS 19×2 мм','м',55],['Ручка мебельная 160 мм','шт',350]]],['Столешница',[['Столешница EGGER 38 мм','м',2550],['Обработка и вырез под мойку','шт',2500],['Монтаж столешницы','м.п.',1200]]],['Фурнитура',[['Петля CLIP top BLUMOTION','компл.',650],['Направляющие TANDEM BLUMOTION 550 мм','компл.',1833],['Профиль GOLA горизонтальный','м',1450],['Опора регулируемая 100 мм','шт',45]]],['Мойка и подключение',[['Мойка кухонная','шт',8500],['Смеситель кухонный','шт',6500]]]],
 wardrobe2:[['Корпус',[['ЛДСП 16 мм — корпус','лист',1830],['Кромка ABS 19×0,4 мм','м',17],['Задняя стенка ДВП','м²',180],['Сборка шкафа','шт',4200]]],['Двери',[['Фасад МДФ','м²',4200],['Профиль алюминиевый','м',1200],['Ручка мебельная 160 мм','шт',350]]],['Наполнение',[['Полка ЛДСП','шт',750],['Штанга для одежды','м',650],['Ящик с направляющими','компл.',2800]]],['Фурнитура',[['Петля CLIP top 110°','компл.',294],['Стяжка Minifix','компл.',35]]]],
 vanity:[['Корпус',[['ЛДСП 16 мм — корпус','лист',1830],['Кромка ABS 19×0,4 мм','м',17],['Задняя стенка ДВП','м²',180],['Сборка тумбы','шт',2800]]],['Фасады',[['Фасад МДФ','м²',4200],['Ручка мебельная 160 мм','шт',350]]],['Столешница',[['Столешница компакт HPL','м',3900],['Вырез под раковину','шт',1800]]],['Фурнитура',[['Петля CLIP top BLUMOTION','компл.',650],['Направляющие TANDEM BLUMOTION','компл.',1833]]]]
};
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
const money=n=>new Intl.NumberFormat('ru-RU',{minimumFractionDigits:2,maximumFractionDigits:2}).format(Number(n)||0)+' ₽';
const num=n=>{const x=Number(String(n??'').replace(',','.'));return Number.isFinite(x)?x:0};
const uid=()=>Date.now().toString(36)+Math.random().toString(36).slice(2,7);

function save(){localStorage.setItem(KEY,JSON.stringify(state));}
function load(){try{const x=JSON.parse(localStorage.getItem(KEY)||'null');if(x&&Array.isArray(x.sections)){state.sections=x.sections;state.winter=!!x.winter;state.tight=!!x.tight;state.custom=num(x.custom)||1;state.method=x.method||'resource';}}catch(e){}}
function section(name='Новый раздел'){return {id:uid(),name,items:[]};}
function item(name='Новая позиция',unit='шт',price=0,quantity=1){return {id:uid(),name,unit,price:num(price),quantity:Math.max(.001,num(quantity)||1)};}

function calc(){
 let direct=0,count=0;state.sections.forEach(s=>s.items.forEach(i=>{direct+=num(i.quantity)*num(i.price);count++}));
 const winter=state.winter?1.12:1,tight=state.tight?1.08:1,custom=Math.max(.001,num(state.custom)||1),coeff=winter*tight*custom;
 const base=direct*coeff,over=base*.15,profit=base*.08,vat=(base+over+profit)*.2,total=base+over+profit+vat;
 return {direct,base,over,profit,vat,total,coeff,count};
}
function render(){
 const list=document.getElementById('smEstimateList');
 list.innerHTML='';
 state.sections.forEach((s,si)=>{
  const direct=s.items.reduce((a,i)=>a+num(i.quantity)*num(i.price),0);
  const group=document.createElement('div');group.className='sm-group';group.dataset.sid=s.id;
  group.innerHTML='<div class="sm-group-head"><span class="sm-group-number">'+String(si+1).padStart(2,'0')+'</span><h3>'+esc(s.name)+'</h3><small>'+s.items.length+' позиций</small><strong>'+money(direct)+'</strong><button class="sm-group-menu" data-action="delete-section" title="Удалить раздел"><i class="bi bi-three-dots"></i></button></div><div class="sm-table"><div class="sm-table-head"><span>РАБОТА / МАТЕРИАЛ</span><span>ОБЪЁМ</span><span>ЕД.</span><span>ЦЕНА</span><span>СУММА</span><span></span></div></div>';
  const table=group.querySelector('.sm-table');
  s.items.forEach((it,ii)=>{
   const row=document.createElement('div');row.className='sm-row';row.dataset.iid=it.id;
   row.innerHTML='<div class="sm-work"><i class="sm-dot"></i><span>'+esc(it.name)+'</span></div><span>'+num(it.quantity).toLocaleString('ru-RU')+'</span><span>'+esc(it.unit)+'</span><span>'+money(it.price)+'</span><strong>'+money(num(it.quantity)*num(it.price))+'</strong><div class="sm-actions"><button data-action="edit-item" title="Изменить"><i class="bi bi-pencil"></i></button><button data-action="delete-item" title="Удалить"><i class="bi bi-trash3"></i></button></div>';
   table.appendChild(row);
  });
  const add=document.createElement('button');add.type='button';add.className='sm-add-row';add.dataset.action='catalog';add.dataset.sid=s.id;add.innerHTML='<i class="bi bi-plus-lg"></i> Добавить работу из каталога';
  table.appendChild(add);
  const manual=document.createElement('button');manual.type='button';manual.className='sm-add-row secondary';manual.dataset.action='manual';manual.dataset.sid=s.id;manual.innerHTML='<i class="bi bi-pencil-square"></i> Добавить вручную';
  table.appendChild(manual);
  list.appendChild(group);
 });
 if(!state.sections.length)list.innerHTML='<div class="sm-empty"><i class="bi bi-calculator"></i><h3>Смета пока пустая</h3><p>Создайте раздел и добавьте работы или материалы из каталога.</p><button class="sm-primary" type="button" id="smEmptyAdd"><i class="bi bi-plus-lg"></i> Создать раздел</button></div>';
 const c=calc();
 document.getElementById('smSectionsCount').textContent=state.sections.length;
 document.getElementById('smItemsCount').textContent=c.count;
 document.getElementById('smDirectShort').textContent=money(c.direct);
 document.getElementById('smDirect').textContent=money(c.direct);
 document.getElementById('smOverhead').textContent=money(c.over);
 document.getElementById('smProfit').textContent=money(c.profit);
 document.getElementById('smCoeff').textContent='× '+c.coeff.toFixed(3);
 document.getElementById('smVat').textContent=money(c.vat);
 document.getElementById('smTotal').textContent=money(c.total);
 document.getElementById('smGrand').textContent=new Intl.NumberFormat('ru-RU',{maximumFractionDigits:0}).format(c.total)+' ₽';
 save();
}
function addSection(){state.sections.push(section());render();setTimeout(()=>editSection(state.sections.at(-1).id),0);}
function editSection(id){
 const s=state.sections.find(x=>x.id===id);if(!s)return;
 const name=prompt('Название раздела',s.name);if(name===null)return;
 if(name.trim()){s.name=name.trim();render();}
}
function manualAdd(sid){
 const name=prompt('Наименование позиции','Новая работа');if(!name)return;
 const quantity=prompt('Количество','1');const unit=prompt('Единица','шт');const price=prompt('Цена','0');
 const s=state.sections.find(x=>x.id===sid);if(s){s.items.push(item(name,unit,price,quantity));render();}
}
function editItem(sid,iid){
 const s=state.sections.find(x=>x.id===sid),it=s?.items.find(x=>x.id===iid);if(!it)return;
 const name=prompt('Наименование',it.name);if(name===null)return;
 const quantity=prompt('Количество',it.quantity);if(quantity===null)return;
 const unit=prompt('Единица',it.unit);if(unit===null)return;
 const price=prompt('Цена',it.price);if(price===null)return;
 it.name=name.trim()||it.name;it.quantity=Math.max(.001,num(quantity)||1);it.unit=unit.trim()||it.unit;it.price=Math.max(0,num(price));render();
}
let selected={sid:null,data:null};
function openCatalog(sid){selected.sid=sid;selected.data=null;document.getElementById('smSelected').hidden=true;document.getElementById('smCatalogAdd').disabled=true;document.getElementById('smCatalogSearch').value='';filterCatalog();bootstrap.Modal.getOrCreateInstance(document.getElementById('smCatalogModal')).show();setTimeout(()=>document.getElementById('smCatalogSearch').focus(),300);}
function filterCatalog(){const q=document.getElementById('smCatalogSearch').value.toLowerCase();document.querySelectorAll('.sm-catalog-item').forEach(x=>x.hidden=!!q&&!x.dataset.name.includes(q));}
function addSelected(){if(!selected.sid||!selected.data)return;const s=state.sections.find(x=>x.id===selected.sid);if(!s)return;const q=num(document.getElementById('smSelectedQty').value)||1;s.items.push(item(selected.data.title,selected.data.unit,selected.data.price,q));bootstrap.Modal.getInstance(document.getElementById('smCatalogModal'))?.hide();render();}
function applyTemplate(type){const data=templates[type];if(!data)return;data.forEach(([name,rows])=>{const s=section(name);rows.forEach(([n,u,p])=>s.items.push(item(n,u,p,1)));state.sections.push(s)});bootstrap.Modal.getInstance(document.getElementById('smTemplateModal'))?.hide();render();}
function exportPrint(){window.print();}
function loadSaved(){load();document.getElementById('smWinter').checked=state.winter;document.getElementById('smTight').checked=state.tight;document.getElementById('smCustom').value=state.custom;document.getElementById('smMethod').value=state.method;render();}
document.addEventListener('click',e=>{
 const t=e.target.closest('[data-action]');if(!t)return;
 const g=t.closest('.sm-group'),sid=t.dataset.sid||g?.dataset.sid,action=t.dataset.action;
 if(action==='delete-section'){if(confirm('Удалить раздел и все его позиции?')){state.sections=state.sections.filter(s=>s.id!==sid);render();}}
 if(action==='catalog')openCatalog(sid);
 if(action==='manual')manualAdd(sid);
 if(action==='edit-item'){const row=t.closest('.sm-row');editItem(sid,row.dataset.iid);}
 if(action==='delete-item'){const row=t.closest('.sm-row');const s=state.sections.find(x=>x.id===sid);if(s&&confirm('Удалить позицию?')){s.items=s.items.filter(i=>i.id!==row.dataset.iid);render();}}
});
document.getElementById('smAddSection').onclick=addSection;
document.getElementById('smEmptyAdd')?.addEventListener('click',addSection);
document.getElementById('smClear').onclick=()=>{if(confirm('Очистить всю текущую смету?')){state.sections=[];state.winter=false;state.tight=false;state.custom=1;render();document.getElementById('smWinter').checked=false;document.getElementById('smTight').checked=false;document.getElementById('smCustom').value=1;}};
document.getElementById('smPrint').onclick=exportPrint;
document.getElementById('smSave').onclick=()=>{save();const b=document.getElementById('smSave');const old=b.innerHTML;b.innerHTML='<i class="bi bi-check2"></i> Сохранено';setTimeout(()=>b.innerHTML=old,1300);};
document.getElementById('smWinter').onchange=e=>{state.winter=e.target.checked;render()};
document.getElementById('smTight').onchange=e=>{state.tight=e.target.checked;render()};
document.getElementById('smCustom').oninput=e=>{state.custom=Math.max(.1,num(e.target.value)||1);render()};
document.getElementById('smMethod').onchange=e=>{state.method=e.target.value;render()};
document.getElementById('smCatalogSearch').oninput=filterCatalog;
document.getElementById('smCatalogGrid').onclick=e=>{const b=e.target.closest('.sm-catalog-item');if(!b)return;document.querySelectorAll('.sm-catalog-item.selected').forEach(x=>x.classList.remove('selected'));b.classList.add('selected');selected.data=b.dataset;document.getElementById('smSelectedName').textContent=b.dataset.title;document.getElementById('smSelected').hidden=false;document.getElementById('smCatalogAdd').disabled=false;};
document.getElementById('smCatalogAdd').onclick=addSelected;
document.getElementById('smTemplates').onclick=e=>{const b=e.target.closest('[data-template]');if(b)applyTemplate(b.dataset.template)};
document.addEventListener('click',e=>{if(e.target.closest('#smEmptyAdd'))addSection()});
loadSaved();
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php';