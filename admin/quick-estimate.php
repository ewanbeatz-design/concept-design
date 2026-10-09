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
$makmartSeed=[
['hardware','MAKMART Корсо XL — петля 95° накладная с доводчиком','MAKMART','MH.714.21.S00.N','шт',177.81],
['hardware','MAKMART Корсо — петля 155° накладная с доводчиком','MAKMART','MH.624.21.W00.BN','шт',455.63],
['hardware','MAKMART M’АртБокс 13M Push — комплект ящика H=178 L=450','MAKMART','M-ART.13M.178450.P.OG','компл.',3461.71],
['hardware','MAKMART M’Арт — механизм открывания от нажатия','MAKMART','M-ART.POD','шт',2677.57],
['hardware','MAKMART ручка-скоба 160 мм — платина','MAKMART','8.1175.0160.0911','шт',1059.25],
];
foreach($makmartSeed as $x){
    $q=$pdo->prepare("SELECT id FROM quick_estimate_catalog WHERE brand=? AND article=? LIMIT 1");
    $q->execute([$x[2],$x[3]]);
    if(!$q->fetchColumn()){
        $s=$pdo->prepare("INSERT INTO quick_estimate_catalog(category,name,brand,article,unit,price) VALUES(?,?,?,?,?,?)");
        $s->execute($x);
    }
}
if (!(int)$pdo->query("SELECT COUNT(*) FROM quick_estimate_catalog")->fetchColumn()) {
    $s=$pdo->prepare("INSERT INTO quick_estimate_catalog(category,name,brand,article,unit,price) VALUES(?,?,?,?,?,?)");
    foreach($seed as $x)$s->execute($x);
}
$cat=$pdo->query("SELECT * FROM quick_estimate_catalog WHERE active=1 ORDER BY FIELD(category,'materials','countertop','hardware','fasteners'),name")->fetchAll();
$labels=['materials'=>'ЛДСП / МДФ / Кромка','countertop'=>'Столешницы','hardware'=>'Фурнитура','fasteners'=>'Крепёж и расходники','electro'=>'Электрика'];
function qem($n){return number_format((float)$n,2,',',' ').' ₽';}
?>

<div class="sm-projects-view" id="smProjectsView">
    <div class="sm-projects-hero">
        <div>
            <div class="sm-eyebrow">РАБОЧЕЕ ПРОСТРАНСТВО / СМЕТА</div>
            <h1>Мои проекты</h1>
            <p>Все ваши расчёты в одном месте. Откройте объект, чтобы продолжить работу со сметой.</p>
        </div>
        <button class="sm-primary" type="button" id="smCreateProject"><i class="bi bi-plus-lg"></i> Новый проект</button>
    </div>
    <div class="sm-projects-metrics">
        <div class="sm-project-metric"><div class="sm-project-metric-icon terra"><i class="bi bi-grid-3x3-gap-fill"></i></div><div><span>Всего проектов</span><strong id="smProjectsCount">0</strong><small>сметных объектов</small></div></div>
        <div class="sm-project-metric"><div class="sm-project-metric-icon sage"><i class="bi bi-activity"></i></div><div><span>В работе</span><strong id="smProjectsActive">0</strong><small>активных смет</small></div></div>
        <div class="sm-project-metric"><div class="sm-project-metric-icon sand"><i class="bi bi-wallet2"></i></div><div><span>Стоимость смет</span><strong id="smProjectsTotal">0 ₽</strong><small>по всем объектам</small></div></div>
    </div>
    <div class="sm-projects-section-head">
        <div><h2>Проекты <span id="smProjectsBadge">0</span></h2><p>Откройте объект, чтобы перейти в рабочее пространство сметы.</p></div>
        <label class="sm-project-search"><i class="bi bi-search"></i><input id="smProjectSearch" placeholder="Поиск проекта"></label>
    </div>
    <div class="project-grid sm-project-grid" id="smProjectGrid"></div>
</div>

<div class="sm-estimate-page" id="smEstimateApp">
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
            <button class="sm-outline" type="button" id="smBackProjects"><i class="bi bi-grid-3x3-gap"></i> Мои проекты</button>
            <button class="sm-primary" type="button" id="smNewEstimate"><i class="bi bi-plus-lg"></i> Новый проект</button>
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
            <button type="button" id="smCatalogToolbar"><i class="bi bi-book"></i> Каталог</button>
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

<div class="modal fade" id="smProjectModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content sm-modal"><div class="modal-header"><div><div class="sm-eyebrow" id="smProjectModalEyebrow">НОВЫЙ ПРОЕКТ</div><h5 class="modal-title" id="smProjectModalTitle">Создать проект</h5><small>Добавьте заказчика и объект — данные сохранятся отдельно от остальных смет.</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="smProjectEditId"><div class="sm-form-grid"><label><span>Название проекта</span><input id="smProjectName" class="form-control" placeholder="Например, Кухня для Ивановых"></label><label><span>Заказчик</span><input id="smProjectClient" class="form-control" placeholder="Имя заказчика"></label><label><span>Телефон заказчика</span><input id="smProjectPhone" class="form-control" type="tel" autocomplete="tel" placeholder="+7 900 000-00-00"></label><label class="sm-form-wide"><span>Объект / адрес</span><input id="smProjectObject" class="form-control" placeholder="Квартира, дом, адрес"></label><label><span>Тип проекта</span><select id="smProjectType" class="form-select"><option value="Шкаф">Шкаф</option><option value="Кухня">Кухня</option><option value="Стол">Стол</option><option value="Пенал">Пенал</option><option value="Гардеробная">Гардеробная</option><option value="Тумба">Тумба</option><option value="Комод">Комод</option><option value="Прихожая">Прихожая</option><option value="Мебель для ванной">Мебель для ванной</option><option value="Другое">Другое</option></select></label></div></div><div class="modal-footer"><button class="sm-outline" type="button" data-bs-dismiss="modal">Отмена</button><button class="sm-primary" type="button" id="smProjectCreateConfirm"><i class="bi bi-plus-lg"></i> Создать проект</button></div></div></div></div>

<div class="modal fade" id="smConfirmModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content sm-modal sm-confirm-modal"><div class="modal-body"><div class="sm-confirm-icon"><i class="bi bi-question-lg"></i></div><h5 id="smConfirmTitle">Подтвердите действие</h5><p id="smConfirmText">Вы уверены?</p></div><div class="modal-footer"><button class="sm-outline" type="button" data-bs-dismiss="modal">Отмена</button><button class="sm-primary" type="button" id="smConfirmOk">Продолжить</button></div></div></div></div>

<div class="modal fade" id="smStageModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content sm-modal"><div class="modal-header"><div><div class="sm-eyebrow">ЭТАПЫ</div><h5 class="modal-title">Прогресс проекта</h5><small>Укажите количество выполненных этапов.</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="smStageProjectId"><label><span style="display:block;font-size:11px;font-weight:700;color:var(--muted);margin-bottom:7px">Выполнено этапов</span><input id="smStageValue" class="form-control" type="number" min="0" max="9" step="1"></label></div><div class="modal-footer"><button class="sm-outline" type="button" data-bs-dismiss="modal">Отмена</button><button class="sm-primary" type="button" id="smStageSave">Сохранить</button></div></div></div></div>

<div class="modal fade" id="smEntryModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content sm-modal"><div class="modal-header"><div><div class="sm-eyebrow" id="smEntryEyebrow">РУЧНОЙ ВВОД</div><h5 class="modal-title" id="smEntryTitle">Добавить позицию</h5><small id="smEntryHint">Заполните параметры позиции.</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="smEntrySid"><input type="hidden" id="smEntryIid"><div class="sm-form-grid"><label class="sm-form-wide"><span>Наименование</span><input id="smEntryName" class="form-control" placeholder="Например, Монтаж столешницы"></label><label><span>Количество</span><input id="smEntryQty" class="form-control" type="number" min=".001" step=".001" value="1"></label><label><span>Единица измерения</span><input id="smEntryUnit" class="form-control" value="шт" placeholder="шт, м, м²..."></label><label class="sm-form-wide"><span>Цена за единицу, ₽</span><input id="smEntryPrice" class="form-control" type="number" min="0" step=".01" value="0"></label></div></div><div class="modal-footer"><button class="sm-outline" type="button" data-bs-dismiss="modal">Отмена</button><button class="sm-primary" type="button" id="smEntrySave"><i class="bi bi-check2"></i> Сохранить</button></div></div></div></div>

<div class="modal fade" id="smSectionModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content sm-modal"><div class="modal-header"><div><div class="sm-eyebrow">РАЗДЕЛ СМЕТЫ</div><h5 class="modal-title">Название раздела</h5><small>Например: Корпус, Фасады, Фурнитура.</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="smSectionId"><label><span style="display:block;font-size:11px;font-weight:700;color:var(--muted);margin-bottom:7px">Название</span><input id="smSectionName" class="form-control" placeholder="Новый раздел"></label></div><div class="modal-footer"><button class="sm-outline" type="button" data-bs-dismiss="modal">Отмена</button><button class="sm-primary" type="button" id="smSectionSave"><i class="bi bi-check2"></i> Сохранить</button></div></div></div></div>

<div class="modal fade" id="smCatalogModal" tabindex="-1">
 <div class="modal-dialog modal-dialog-centered modal-xl">
  <div class="modal-content sm-modal">
   <div class="modal-header">
    <div><div class="sm-eyebrow" id="smCatalogEyebrow">КАТАЛОГ</div><h5 class="modal-title" id="smCatalogTitle">Добавить работу или материал</h5><small id="smCatalogSubtitle">Выберите позицию и добавьте её в нужный раздел.</small></div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
   </div>
   <div class="modal-body">
    <div class="sm-catalog-search"><i class="bi bi-search"></i><input id="smCatalogSearch" placeholder="Поиск материала, артикула, бренда..."></div><div class="sm-catalog-hint" id="smCatalogHint"><i class="bi bi-mouse2"></i> Двойной клик по позиции — сразу добавить в смету</div>
    <div class="sm-catalog-grid" id="smCatalogGrid">
    <?php foreach($cat as $x): ?>
      <button type="button" class="sm-catalog-item" data-id="<?=$x['id']?>" data-category="<?=h($labels[$x['category']] ?? $x['category'])?>" data-name="<?=h(mb_strtolower($x['name'].' '.$x['brand'].' '.$x['article']))?>" data-title="<?=h($x['name'])?>" data-brand="<?=h($x['brand'])?>" data-unit="<?=h($x['unit'])?>" data-price="<?=$x['price']?>">
       <span class="sm-cat-icon"><i class="bi bi-tools"></i></span>
       <span class="sm-cat-info"><strong><?=h($x['name'])?></strong><small><?=h($labels[$x['category']] ?? $x['category'])?> · <?=h($x['brand'])?> · <?=h($x['article'])?></small></span>
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
      <button type="button" class="sm-template" data-template="wardrobe"><span class="sm-template-icon"><i class="bi bi-door-closed"></i></span><strong>Шкаф-купе</strong><small>Корпус · Фасады · Наполнение · Фурнитура</small><b>12 позиций</b></button>
      <button type="button" class="sm-template" data-template="kitchen"><span class="sm-template-icon"><i class="bi bi-layout-text-sidebar-reverse"></i></span><strong>Кухня</strong><small>Корпус · Фасады · Столешница · Фурнитура</small><b>16 позиций</b></button>
      <button type="button" class="sm-template" data-template="wardrobe2"><span class="sm-template-icon"><i class="bi bi-grid-3x3-gap"></i></span><strong>Распашной шкаф</strong><small>Корпус · Двери · Полки · Петли · Ручки</small><b>11 позиций</b></button>
      <button type="button" class="sm-template" data-template="vanity"><span class="sm-template-icon"><i class="bi bi-droplet"></i></span><strong>Тумба под раковину</strong><small>Корпус · Фасады · Столешница · Фурнитура</small><b>9 позиций</b></button>
    </div>
    <div class="sm-custom-template-area">
     <div class="sm-custom-template-head"><div><strong>Мои шаблоны</strong><small>Ваши сохранённые варианты смет</small></div><button class="sm-primary" type="button" id="smSaveTemplateOpen"><i class="bi bi-plus-lg"></i> Создать свой шаблон</button></div>
     <div class="sm-template-grid" id="smCustomTemplates"></div>
     <p class="sm-custom-template-empty" id="smCustomTemplatesEmpty">Здесь появятся шаблоны, которые вы сохраните из текущей сметы.</p>
    </div>
   </div>
   <div class="modal-footer"><button class="sm-outline" type="button" data-bs-dismiss="modal">Отмена</button></div>
  </div>
 </div>
</div>

<div class="modal fade" id="smSaveTemplateModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
   <div class="modal-content sm-modal">
    <div class="modal-header"><div><div class="sm-eyebrow">МОИ ШАБЛОНЫ</div><h5 class="modal-title">Сохранить шаблон сметы</h5><small>Сохранятся разделы, позиции, количество и цены.</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
     <label class="sm-template-name-label" for="smTemplateName">Название шаблона</label>
     <input class="form-control" id="smTemplateName" maxlength="100" placeholder="Например, шкаф в прихожую" autocomplete="off">
     <p class="sm-template-save-error" id="smTemplateSaveError" hidden>Добавь хотя бы один раздел с позициями в смету.</p>
    </div>
    <div class="modal-footer"><button class="sm-outline" type="button" data-bs-dismiss="modal">Отмена</button><button class="sm-primary" type="button" id="smSaveTemplateConfirm"><i class="bi bi-check2"></i> Сохранить шаблон</button></div>
   </div>
  </div>
 </div>

 <style>
.sm-projects-view{max-width:1580px;margin:0 auto}.sm-projects-hero{display:flex;justify-content:space-between;align-items:flex-end;gap:24px;margin:4px 0 28px}.sm-projects-hero h1{font-size:32px;line-height:1.05;letter-spacing:-.04em;margin:5px 0 0;color:var(--ink)}.sm-projects-hero p{margin:8px 0 0;color:var(--muted);font-size:12px;max-width:650px}.sm-projects-metrics{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:32px}.sm-project-metric{display:flex;align-items:center;gap:14px;padding:18px 20px;background:var(--bg-2);border:1px solid var(--line);border-radius:16px;min-height:92px}.sm-project-metric-icon{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;flex:0 0 46px;font-size:18px}.sm-project-metric-icon.terra{background:rgba(199,157,109,.13);color:var(--gold-2)}.sm-project-metric-icon.sage{background:rgba(88,150,116,.13);color:#8bd0a8}.sm-project-metric-icon.sand{background:rgba(218,194,151,.13);color:#e8d4aa}.sm-project-metric span{display:block;color:var(--muted);font-size:11px}.sm-project-metric strong{display:block;font-size:24px;line-height:1.1;margin:3px 0;font-weight:800;letter-spacing:-.03em}.sm-project-metric small{display:block;color:var(--muted-2);font-size:10px}.sm-projects-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:17px}.sm-projects-section-head h2{font-size:19px;letter-spacing:-.02em;margin:0;font-weight:800}.sm-projects-section-head h2 span{display:inline-flex;align-items:center;justify-content:center;min-width:24px;height:24px;margin-left:5px;padding:0 7px;border-radius:100px;background:var(--bg-3);border:1px solid var(--line);font-size:11px;color:var(--muted)}.sm-projects-section-head p{margin:5px 0 0;color:var(--muted);font-size:11px}.sm-project-search{position:relative;width:270px}.sm-project-search i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--muted-2);font-size:13px}.sm-project-search input{width:100%;height:40px;padding:0 13px 0 36px;border:1px solid var(--line);border-radius:11px;background:var(--bg-2);color:var(--ink);font-size:12px;outline:0}.sm-project-search input:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(199,157,109,.08)}.project-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.project-card{min-width:0}.project-card .card-top{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:28px}.project-card .card-top>.text-muted{margin-left:auto;text-align:right;white-space:nowrap}.project-card .card-top,.project-card .project-info,.project-card .project-stage,.project-card .progress-meta,.project-card .progress-track,.project-card .card-footer,.project-card .project-card-actions{position:relative}.status-active{display:inline-flex;align-items:center;gap:7px}.status-active i{width:6px;height:6px;border-radius:50%;background:#6fc48d}.mini-avatar{width:28px;height:28px;border-radius:50%;display:inline-grid;place-items:center;background:rgba(199,157,109,.16);color:var(--gold-2);font-size:11px;font-weight:800;margin-right:8px}.client-line{display:flex;align-items:center;color:var(--muted);font-size:11px}.project-stage{margin-top:17px;padding-top:15px;border-top:1px solid var(--line)}.project-stage-head{display:flex;justify-content:space-between;align-items:center;font-size:10px;color:var(--muted)}.project-stage-head span{display:flex;align-items:center;gap:7px}.project-stage-head strong{color:var(--ink);font-size:11px}.project-stage-title{font-size:11px;font-weight:700;margin:9px 0 8px}.project-stage-track,.progress-track{height:5px;background:var(--bg-3);border-radius:99px;overflow:hidden}.project-stage-track span,.progress-track span{display:block;height:100%;border-radius:inherit;background:var(--gold-2);transition:width .25s ease}.progress-meta{display:flex;justify-content:space-between;margin-top:14px;font-size:10px;color:var(--muted)}.progress-meta strong{color:var(--ink)}.project-card-actions{display:flex;gap:8px;margin-top:15px}.project-open-link,.stage-manage-button{flex:1;display:flex;align-items:center;justify-content:space-between;gap:8px;border:1px solid var(--line);background:var(--bg-3);color:var(--ink);border-radius:10px;padding:9px 11px;font-size:10px;text-decoration:none;cursor:pointer}.stage-manage-button{justify-content:center;color:var(--muted)}.project-open-link:hover,.stage-manage-button:hover{border-color:var(--line-2);color:var(--ink)}@media(max-width:1100px){.project-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:680px){.project-grid{grid-template-columns:1fr}}.sm-project-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.sm-est-project-card{background:var(--bg-2);border:1px solid var(--line);border-radius:18px;padding:18px;transition:.22s var(--ease);cursor:pointer}.sm-est-project-card:hover{transform:translateY(-3px);border-color:var(--line-2);box-shadow:0 18px 50px rgba(0,0,0,.16)}.sm-est-project-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}.sm-est-status{display:inline-flex;align-items:center;gap:7px;padding:6px 10px;border-radius:100px;background:rgba(111,196,141,.1);color:#8bd0a8;font-size:10px;font-weight:700}.sm-est-status i{width:6px;height:6px;border-radius:50%;background:#6fc48d}.sm-est-project-date{font-size:9px;color:var(--muted-2)}.sm-est-project-icon{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;background:rgba(199,157,109,.1);color:var(--gold-2);font-size:19px;margin-bottom:13px}.sm-est-project-card h3{font-size:17px;margin:0;color:var(--ink);letter-spacing:-.025em}.sm-est-project-card p{height:32px;margin:6px 0 14px;color:var(--muted);font-size:10px;line-height:1.55}.sm-est-project-info{display:grid;grid-template-columns:1fr 1fr;gap:8px;border-top:1px solid var(--line);padding-top:12px}.sm-est-project-info span{display:block;color:var(--muted-2);font-size:9px}.sm-est-project-info strong{display:block;margin-top:3px;color:var(--ink);font:700 11px monospace}.sm-est-project-progress{margin-top:13px}.sm-est-project-progress>div{display:flex;justify-content:space-between;color:var(--muted);font-size:9px}.sm-est-project-progress strong{color:var(--ink)}.sm-est-project-track{height:5px;border-radius:100px;background:var(--bg-3);overflow:hidden;margin-top:6px}.sm-est-project-track span{display:block;height:100%;background:linear-gradient(90deg,var(--gold),var(--gold-2))}.sm-est-project-footer{display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--line);margin-top:13px;padding-top:12px}.sm-est-project-footer strong{font:700 14px monospace;color:var(--gold-2)}.sm-est-project-footer span{font-size:10px;font-weight:700;color:var(--ink)}.sm-est-project-footer i{color:var(--gold-2);margin-left:5px}.sm-project-manage-actions{display:flex;gap:7px;margin-top:9px}.sm-project-manage-actions button{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;border:1px solid var(--line);border-radius:9px;background:transparent;color:var(--muted);padding:8px 10px;font-size:11px;font-weight:700;cursor:pointer;transition:.18s}.sm-project-manage-actions button:hover{border-color:var(--gold);color:var(--ink);background:rgba(199,157,109,.06)}.sm-project-manage-actions button[data-project-delete]:hover{border-color:#c66b6b;color:#e99a9a;background:rgba(198,107,107,.08)}.sm-new-est-card{min-height:250px;border:1px dashed var(--line-2);border-radius:18px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:30px;color:var(--muted);transition:.22s var(--ease)}.sm-new-est-card:hover{border-color:var(--gold);background:rgba(199,157,109,.035);transform:translateY(-2px)}.sm-new-est-card>span{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;background:rgba(199,157,109,.1);color:var(--gold-2);font-size:20px;margin-bottom:12px}.sm-new-est-card strong{color:var(--ink);font-size:13px}.sm-new-est-card small{font-size:10px;color:var(--muted-2);margin-top:5px;max-width:210px;line-height:1.5}.sm-projects-empty{grid-column:1/-1;padding:65px 25px;text-align:center;border:1px dashed var(--line-2);border-radius:18px;background:var(--bg-2)}.sm-projects-empty i{display:grid;place-items:center;width:58px;height:58px;border-radius:16px;background:rgba(199,157,109,.1);color:var(--gold);margin:0 auto 14px;font-size:22px}.sm-projects-empty strong{font-size:16px}.sm-projects-empty p{color:var(--muted);font-size:11px;margin:6px 0 17px}@media(max-width:1100px){.sm-project-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:760px){.sm-projects-hero{align-items:flex-start;flex-direction:column}.sm-projects-hero .sm-primary{width:100%}.sm-projects-metrics{grid-template-columns:1fr}.sm-projects-section-head{align-items:stretch;flex-direction:column}.sm-project-search{width:100%}.sm-project-grid{grid-template-columns:1fr}}.sm-estimate-page{max-width:1580px;margin:0 auto}.sm-estimate-heading{display:flex;justify-content:space-between;align-items:flex-end;gap:24px;margin-bottom:24px}.sm-eyebrow{font-size:9px;letter-spacing:.16em;color:var(--gold-2);font-weight:800;text-transform:uppercase}.sm-title-row{display:flex;align-items:center;gap:13px}.sm-title-icon{width:48px;height:48px;border-radius:14px;background:rgba(199,157,109,.12);color:var(--gold-2);display:grid;place-items:center;font-size:21px}.sm-title-row h1{font-size:29px;margin:4px 0;color:var(--ink)}.sm-title-row p{margin:0;color:var(--muted);font-size:11px}.sm-heading-actions{display:flex;gap:8px;align-items:center}.sm-outline,.sm-primary{display:inline-flex;align-items:center;justify-content:center;gap:7px;border-radius:9px;padding:9px 13px;font:700 11px inherit;cursor:pointer}.sm-outline{border:1px solid var(--line);background:transparent;color:var(--ink)}.sm-outline:hover{background:var(--bg-3)}.sm-primary{border:1px solid var(--ink);background:var(--ink);color:var(--bg)}.sm-primary:hover{opacity:.9}.sm-summary{background:linear-gradient(135deg,#24231f,#34312a);border:1px solid rgba(199,157,109,.22);border-radius:var(--r);padding:19px 22px;color:#fff;display:flex;align-items:center;gap:28px;margin-bottom:20px}.sm-summary-total{min-width:250px}.sm-summary-total span,.sm-summary-total small{display:block;color:#c8bcae;font-size:9px;letter-spacing:.08em}.sm-summary-total strong{display:block;font:600 28px monospace;margin:6px 0 3px}.sm-summary-stats{display:flex;gap:24px;padding-left:28px;border-left:1px solid rgba(199,157,109,.3)}.sm-summary-stats div{display:grid;gap:2px}.sm-summary-stats b{font:600 14px monospace;color:#fff}.sm-summary-stats span{font-size:9px;color:#c8bcae}.sm-summary-more{margin-left:auto;border:1px solid rgba(255,255,255,.16);background:transparent;color:#fff;border-radius:8px;padding:8px 11px;font-size:10px}.sm-calc-controls{display:flex;align-items:center;gap:16px;flex-wrap:wrap;padding:12px 3px;border-bottom:1px solid var(--line)}.sm-calc-controls>div{display:grid;gap:4px}.sm-control-label{font-size:8px;letter-spacing:.1em;color:var(--muted);font-weight:800}.sm-calc-controls select,.sm-custom input{border:1px solid var(--line);background:var(--bg-2);color:var(--ink);border-radius:7px;padding:7px 9px;font-size:10px}.sm-check{display:flex;align-items:center;gap:6px;color:var(--muted);font-size:10px}.sm-check b{color:var(--gold-2)}.sm-check input{accent-color:var(--gold-2)}.sm-custom{display:flex;align-items:center;gap:7px;font-size:10px;color:var(--muted);margin-left:auto}.sm-custom input{width:70px;text-align:right}.sm-toolbar{display:flex;justify-content:space-between;align-items:center;padding:16px 3px;border-bottom:1px solid var(--line)}.sm-toolbar-tabs,.sm-toolbar-actions{display:flex;align-items:center;gap:6px}.sm-toolbar-tabs button,.sm-toolbar-actions button{border:0;background:transparent;color:var(--muted);font-size:10px;padding:8px 10px;border-radius:7px}.sm-toolbar-tabs button.active{background:var(--bg-3);color:var(--ink);font-weight:800}.sm-toolbar-tabs button.disabled{opacity:.45}.sm-toolbar-tabs span{font-size:8px}.sm-toolbar-actions button{color:var(--ink);font-weight:700}.sm-toolbar-actions button:hover{background:var(--bg-3)}.sm-estimate-list{padding-top:18px}.sm-group{margin-bottom:22px}.sm-group-head{display:grid;grid-template-columns:30px minmax(0,1fr) auto auto 32px;gap:10px;align-items:center;padding:0 9px 10px}.sm-group-number{font:500 10px monospace;color:var(--gold-2)}.sm-group-head h3{margin:0;font-size:13px}.sm-group-head small{font-size:9px;color:var(--muted)}.sm-group-head strong{font:600 11px monospace}.sm-group-menu{border:0;background:transparent;color:var(--muted);font-size:16px}.sm-table{border:1px solid var(--line);border-radius:12px;overflow:hidden;background:var(--bg-2)}.sm-table-head,.sm-row{display:grid;grid-template-columns:minmax(210px,2fr) 110px 75px 125px 135px 80px;gap:12px;align-items:center;padding:0 15px}.sm-table-head{height:34px;background:var(--bg-3);color:var(--muted-2);font:600 8px monospace}.sm-row{min-height:54px;border-top:1px solid var(--line);font-size:11px;color:var(--muted);cursor:pointer}.sm-row:hover{background:rgba(199,157,109,.035)}.sm-work{display:flex;align-items:center;gap:8px;color:var(--ink);font-weight:700}.sm-dot{width:5px;height:5px;border-radius:50%;background:var(--muted-2);flex:0 0 auto}.sm-cell-input{width:100%;border:1px solid var(--line-2);background:var(--bg-3);color:var(--ink);border-radius:6px;padding:7px;font-size:11px}.sm-actions{display:flex;justify-content:flex-end;gap:2px}.sm-actions button{border:0;background:transparent;color:var(--muted);padding:5px}.sm-actions button:hover{color:var(--gold-2)}.sm-add-row{display:flex;gap:8px;align-items:center;border:0;background:transparent;color:var(--gold-2);padding:13px 15px;font-size:10px;font-weight:800}.sm-add-row.secondary{color:var(--muted);padding-top:0}.sm-bottom{display:flex;justify-content:flex-end;margin-top:4px}.sm-totals{width:min(690px,100%);border-top:2px solid var(--line-2);padding-top:7px}.sm-totals>div{display:flex;justify-content:space-between;gap:20px;padding:9px 8px;color:var(--muted);font-size:11px}.sm-totals b{font:600 12px monospace;color:var(--ink)}.sm-grand{border-top:1px solid var(--line-2);margin-top:5px;padding-top:16px!important;color:var(--ink)!important}.sm-grand strong{font:700 22px monospace;color:var(--gold-2)}.sm-empty{border:1px dashed var(--line-2);border-radius:14px;min-height:280px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:7px;color:var(--muted)}.sm-empty i{font-size:34px;color:var(--gold-2)}.sm-empty h3{font-size:15px;color:var(--ink);margin:6px 0 0}.sm-empty p{font-size:11px;margin:0 0 10px}.sm-modal{border:1px solid var(--line);background:var(--bg-2);color:var(--ink)}.sm-modal .modal-header,.sm-modal .modal-footer{border-color:var(--line)}.sm-modal .modal-title{margin:4px 0 3px;font-size:18px}.sm-modal .modal-header small{font-size:10px;color:var(--muted)}.sm-catalog-search{display:flex;align-items:center;gap:9px;border:1px solid var(--line);background:var(--bg-3);border-radius:10px;padding:0 12px;margin-bottom:14px}.sm-catalog-search input{width:100%;border:0;outline:0;background:transparent;color:var(--ink);padding:11px}.sm-catalog-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;max-height:58vh;overflow:auto}.sm-catalog-item{display:grid;grid-template-columns:34px minmax(0,1fr) auto 20px;gap:9px;align-items:center;text-align:left;border:1px solid var(--line);background:var(--bg-3);color:var(--ink);border-radius:10px;padding:10px}.sm-catalog-item:hover,.sm-catalog-item.selected{border-color:var(--gold);background:rgba(199,157,109,.07)}.sm-cat-icon{width:30px;height:30px;border-radius:8px;background:rgba(199,157,109,.1);display:grid;place-items:center;color:var(--gold-2)}.sm-cat-info{min-width:0}.sm-cat-info strong,.sm-cat-info small{display:block}.sm-cat-info strong{font-size:10px}.sm-cat-info small{font-size:8px;color:var(--muted);margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.sm-catalog-item>strong{font:600 10px monospace;white-space:nowrap}.sm-cat-check{opacity:0;color:var(--gold-2)}.sm-catalog-item.selected .sm-cat-check{opacity:1}.sm-catalog-hint{font-size:9px;color:var(--muted);margin-top:8px}.sm-catalog-hint i{color:var(--gold-2);margin-right:4px}.sm-selected{display:flex;justify-content:space-between;align-items:center;border:1px solid var(--line);border-radius:9px;padding:11px;margin-top:12px;font-size:10px}.sm-selected label{display:flex;align-items:center;gap:7px;color:var(--muted)}.sm-selected input{width:75px;border:1px solid var(--line);background:var(--bg-3);color:var(--ink);border-radius:7px;padding:6px;text-align:right}.sm-template-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}.sm-template{display:grid;grid-template-columns:42px 1fr auto;grid-template-rows:auto auto auto;column-gap:10px;text-align:left;border:1px solid var(--line);background:var(--bg-3);border-radius:12px;padding:14px;color:var(--ink)}.sm-template:hover{border-color:var(--gold)}.sm-template-icon{grid-row:1/4;width:42px;height:42px;border-radius:11px;background:rgba(199,157,109,.11);display:grid;place-items:center;color:var(--gold-2);font-size:18px}.sm-template strong{font-size:12px}.sm-template small{font-size:9px;color:var(--muted);margin-top:4px}.sm-template b{font:600 9px monospace;color:var(--gold-2);margin-top:6px}.sm-custom-template-area{margin-top:22px;padding-top:18px;border-top:1px solid var(--line)}.sm-custom-template-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:12px}.sm-custom-template-head strong,.sm-custom-template-head small{display:block}.sm-custom-template-head strong{font-size:13px}.sm-custom-template-head small{font-size:10px;color:var(--muted);margin-top:4px}.sm-custom-template-head .sm-primary{white-space:nowrap;font-size:10px}.sm-custom-template-empty{font-size:11px;color:var(--muted);padding:14px;border:1px dashed var(--line);border-radius:10px;margin:0}.sm-custom-template-card{position:relative;min-width:0}.sm-custom-template-card .sm-template{width:100%;height:100%;padding-right:42px;cursor:pointer}.sm-custom-template-card .sm-template-delete{position:absolute;right:8px;top:8px;width:27px;height:27px;border:1px solid var(--line);border-radius:7px;background:var(--bg-2);color:var(--muted);z-index:1}.sm-custom-template-card .sm-template-delete:hover{color:#e99a9a;border-color:#c66b6b}.sm-template-name-label{display:block;font-size:11px;font-weight:700;color:var(--muted);margin-bottom:7px}.sm-template-save-error{font-size:11px;color:#e99a9a;margin:8px 0 0}.sm-row.editing{cursor:default}.sm-row.editing .sm-actions button{display:inline-flex}.sm-measure{font-size:9px;color:var(--gold-2);margin-left:3px}@media(max-width:1050px){.sm-summary{flex-wrap:wrap}.sm-summary-more{margin-left:0}.sm-calc-controls{align-items:flex-start}.sm-custom{margin-left:0}.sm-heading-actions{flex-wrap:wrap}.sm-catalog-grid{grid-template-columns:1fr}}@media(max-width:800px){.sm-estimate-heading{flex-direction:column;align-items:flex-start}.sm-heading-actions{width:100%}.sm-heading-actions>*{flex:1}.sm-summary{display:grid;grid-template-columns:1fr}.sm-summary-stats{border-left:0;border-top:1px solid rgba(199,157,109,.3);padding:14px 0 0}.sm-toolbar{align-items:flex-start;gap:10px;flex-direction:column}.sm-table{overflow-x:auto}.sm-table-head,.sm-row{min-width:760px}.sm-template-grid{grid-template-columns:1fr}}@media(max-width:520px){.sm-summary-stats{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}.sm-title-row h1{font-size:23px}.sm-calc-controls{gap:10px}.sm-check,.sm-custom{width:100%}.sm-heading-actions>*{flex:none}.sm-catalog-item{grid-template-columns:30px minmax(0,1fr) 18px}.sm-catalog-item>strong{display:none}}
@media print{.admin-sidebar,.admin-topbar,.sm-estimate-heading,.sm-calc-controls,.sm-toolbar,.sm-summary-more,.sm-actions,.sm-add-row,.modal{display:none!important}.admin-shell{display:block}.admin-content{padding:0}.sm-estimate-page{max-width:none}.sm-summary{margin-bottom:15px}.sm-table{border:1px solid #bbb}.sm-table-head,.sm-row{min-width:0}.sm-grand strong{color:#000}}

.sm-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:15px}.sm-form-grid label{display:block}.sm-form-grid label>span{display:block;font-size:11px;font-weight:700;color:var(--muted);margin:0 0 7px;text-transform:uppercase;letter-spacing:.05em}.sm-form-grid .form-control{height:46px;border:1px solid var(--line);border-radius:12px;background:var(--bg-2);color:var(--ink);font-size:13px;padding:0 13px;box-shadow:none}.sm-form-grid .form-control:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(199,157,109,.1)}.sm-form-wide{grid-column:1/-1}.sm-confirm-modal{text-align:center}.sm-confirm-modal .modal-body{padding:32px 28px 20px}.sm-confirm-icon{width:52px;height:52px;border-radius:16px;display:grid;place-items:center;margin:0 auto 15px;background:rgba(199,157,109,.13);color:var(--gold-2);font-size:21px}.sm-confirm-modal h5{font-size:18px;margin:0 0 7px}.sm-confirm-modal p{margin:0;color:var(--muted);font-size:12px;line-height:1.55}.sm-confirm-modal .modal-footer{justify-content:center;border-top:0;padding:10px 24px 24px}.sm-confirm-modal .sm-primary,.sm-confirm-modal .sm-outline{min-width:125px}@media(max-width:700px){.sm-form-grid{grid-template-columns:1fr}.sm-form-wide{grid-column:auto}}
</style>

<script>
(()=> {
const KEY='concept_standalone_estimate_v2';
const CUSTOM_TEMPLATES_KEY='concept_estimate_custom_templates_v1';
const state={sections:[],winter:false,tight:false,custom:1,method:'resource',projects:[],currentProjectId:null};
let customTemplates=[];
try{const savedTemplates=JSON.parse(localStorage.getItem(CUSTOM_TEMPLATES_KEY)||'[]');customTemplates=Array.isArray(savedTemplates)?savedTemplates:[]}catch(e){customTemplates=[];}
const templates={
 wardrobe:[['Корпус',[['ЛДСП 16 мм — корпус','лист',1830],['Кромка ABS 19×0,4 мм','м',17],['Задняя стенка ДВП','м²',180],['Сборка корпуса шкафа','шт',4500]]],['Фасады',[['Фасад МДФ','м²',4200],['Кромка ABS 19×2 мм','м',55],['Ручка мебельная 160 мм','шт',350]]],['Наполнение',[['Штанга для одежды','м',650],['Полка ЛДСП','шт',750],['Ящик с направляющими','компл.',2800]]],['Фурнитура',[['Петля CLIP top 110°','компл.',294],['Направляющие TANDEM 550 мм','компл.',1371]]]],
 kitchen:[['Корпус',[['ЛДСП 16 мм — корпус','лист',1830],['Кромка ABS 19×0,4 мм','м',17],['Задняя стенка ДВП','м²',180],['Сборка кухонного корпуса','м.п.',3500]]],['Фасады',[['Фасад МДФ крашеный','м²',6800],['Кромка ABS 19×2 мм','м',55],['Ручка мебельная 160 мм','шт',350]]],['Столешница',[['Столешница EGGER 38 мм','м',2550],['Обработка и вырез под мойку','шт',2500],['Монтаж столешницы','м.п.',1200]]],['Фурнитура',[['Петля CLIP top BLUMOTION','компл.',650],['Направляющие TANDEM BLUMOTION 550 мм','компл.',1833],['Профиль GOLA горизонтальный','м',1450],['Опора регулируемая 100 мм','шт',45]]],['Мойка и подключение',[['Мойка кухонная','шт',8500],['Смеситель кухонный','шт',6500]]]],
 wardrobe2:[['Корпус',[['ЛДСП 16 мм — корпус','лист',1830],['Кромка ABS 19×0,4 мм','м',17],['Задняя стенка ДВП','м²',180],['Сборка шкафа','шт',4200]]],['Двери',[['Фасад МДФ','м²',4200],['Профиль алюминиевый','м',1200],['Ручка мебельная 160 мм','шт',350]]],['Наполнение',[['Полка ЛДСП','шт',750],['Штанга для одежды','м',650],['Ящик с направляющими','компл.',2800]]],['Фурнитура',[['Петля CLIP top 110°','компл.',294],['Стяжка Minifix','компл.',35]]]],
 vanity:[['Корпус',[['ЛДСП 16 мм — корпус','лист',1830],['Кромка ABS 19×0,4 мм','м',17],['Задняя стенка ДВП','м²',180],['Сборка тумбы','шт',2800]]],['Фасады',[['Фасад МДФ','м²',4200],['Ручка мебельная 160 мм','шт',350]]],['Столешница',[['Столешница компакт HPL','м',3900],['Вырез под раковину','шт',1800]]],['Фурнитура',[['Петля CLIP top BLUMOTION','компл.',650],['Направляющие TANDEM BLUMOTION','компл.',1833]]]]
}
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
const money=n=>new Intl.NumberFormat('ru-RU',{minimumFractionDigits:2,maximumFractionDigits:2}).format(Number(n)||0)+' ₽';
const num=n=>{const x=Number(String(n??'').replace(',','.'));return Number.isFinite(x)?x:0};
const uid=()=>Date.now().toString(36)+Math.random().toString(36).slice(2,7);

function save(){if(state.currentProjectId){const p=state.projects.find(x=>x.id===state.currentProjectId);if(p){p.sections=JSON.parse(JSON.stringify(state.sections));p.winter=state.winter;p.tight=state.tight;p.custom=state.custom;p.method=state.method;}}localStorage.setItem(KEY,JSON.stringify(state));}
function projectTotal(p){let d=0;(p.sections||[]).forEach(s=>(s.items||[]).forEach(i=>d+=num(i.quantity)*num(i.price)));const coeff=(p.winter?1.12:1)*(p.tight?1.08:1)*(Math.max(.001,num(p.custom)||1));const base=d*coeff,over=base*.15,profit=base*.08;return base+over+profit+(base+over+profit)*.2;}
function snapshotCurrent(){return JSON.parse(JSON.stringify({sections:state.sections,winter:state.winter,tight:state.tight,custom:state.custom,method:state.method}));}
function renderProjects(){
 const grid=document.getElementById('smProjectGrid');if(!grid)return;
 const q=(document.getElementById('smProjectSearch')?.value||'').toLowerCase().trim();
 const projects=state.projects.filter(p=>!q||p.name.toLowerCase().includes(q));
 const total=state.projects.reduce((a,p)=>a+projectTotal(p),0);
 document.getElementById('smProjectsCount').textContent=state.projects.length;
 document.getElementById('smProjectsActive').textContent=state.projects.length;
 document.getElementById('smProjectsTotal').textContent=new Intl.NumberFormat('ru-RU',{maximumFractionDigits:0}).format(total)+' ₽';
 document.getElementById('smProjectsBadge').textContent=state.projects.length;
 grid.innerHTML='';
 if(!projects.length){grid.innerHTML='<div class="sm-projects-empty"><i class="bi bi-grid-3x3-gap"></i><strong>Проектов пока нет</strong><p>Создайте первый проект и начните собирать смету.</p><button type="button" class="sm-primary" id="smEmptyProject"><i class="bi bi-plus-lg"></i> Создать проект</button></div>';return;}
 projects.forEach(p=>{
   const totalP=projectTotal(p), items=(p.sections||[]).reduce((a,s)=>a+(s.items||[]).length,0);
   const stagesTotal=Math.max(1,num(p.stagesTotal)||9), stagesDone=Math.min(stagesTotal,Math.max(0,num(p.stagesDone)||0));
   const stagePct=Math.round(stagesDone/stagesTotal*100), estimatePct=items?100:0;
   const client=(p.client||'').trim(), avatar=esc((client||p.name||'П').charAt(0).toUpperCase());
   const card=document.createElement('article');card.className='project-card sm-est-project-card';card.dataset.projectId=p.id;
   card.innerHTML='<div class="card-top"><span class="status status-active"><i></i>В работе</span><span class="text-muted small">'+esc(p.type||'Строительство')+'</span></div><div class="project-info"><h3>'+esc(p.name)+'</h3><span>'+esc(p.objectName||'Объект не указан')+'</span><div class="client-line mt-3"><span class="mini-avatar">'+avatar+'</span>'+esc(client||'Заказчик не указан')+'</div>'+(p.phone?'<div class="client-line sm-client-phone"><i class="bi bi-telephone me-2"></i>'+esc(p.phone)+'</div>':'')+'</div><div class="project-stage"><div class="project-stage-head"><span><i class="fa-solid fa-bars-progress"></i> Этапы</span><strong>'+stagesDone+' / '+stagesTotal+'</strong></div><div class="project-stage-title">'+esc((p.sections&&p.sections[0]?.name)||'Общестроительные работы')+'</div><div class="project-stage-track"><span style="width:'+stagePct+'%"></span></div></div><div class="progress-meta"><span>Заполнено сметы</span><strong>'+estimatePct+'%</strong></div><div class="progress-track"><span style="width:'+estimatePct+'%"></span></div><div class="card-footer"><span><i class="fa-solid fa-calendar-days"></i> '+esc(p.date||'')+'</span><strong>'+money(totalP)+'</strong></div><div class="project-card-actions"><button type="button" class="project-open-link" data-open-estimate="'+esc(p.id)+'"><span>Открыть проект</span><i class="fa-solid fa-arrow-up-right-from-square"></i></button><button type="button" class="stage-manage-button" data-stage-manage="'+esc(p.id)+'"><i class="fa-solid fa-bars-progress"></i><span>Этапы</span></button></div><div class="sm-project-manage-actions"><button type="button" data-project-edit="'+esc(p.id)+'"><i class="bi bi-pencil-square"></i> Редактировать</button><button type="button" data-project-delete="'+esc(p.id)+'"><i class="bi bi-trash3"></i> Удалить</button></div>';
   card.querySelector('[data-open-estimate]').onclick=e=>{e.stopPropagation();openProject(p.id);};
   card.querySelector('[data-stage-manage]').onclick=e=>{e.stopPropagation();document.getElementById('smStageValue').value=stagesDone;document.getElementById('smStageProjectId').value=p.id;bootstrap.Modal.getOrCreateInstance(document.getElementById('smStageModal')).show();};
   card.querySelector('[data-project-edit]').onclick=e=>{e.stopPropagation();editProject(p.id);};
   card.querySelector('[data-project-delete]').onclick=e=>{e.stopPropagation();deleteProject(p.id);};
   card.onclick=()=>openProject(p.id);grid.appendChild(card);
 });
 const n=document.createElement('button');n.type='button';n.className='sm-new-est-card';n.id='smNewProjectCard';n.innerHTML='<span><i class="bi bi-plus-lg"></i></span><strong>Создать новый проект</strong><small>Добавьте объект и начните собирать смету</small>';n.onclick=createProject;grid.appendChild(n);
}
function createProject(){
 document.getElementById('smProjectEditId').value='';
 document.getElementById('smProjectName').value='';
 document.getElementById('smProjectClient').value='';
 document.getElementById('smProjectPhone').value='';
 document.getElementById('smProjectObject').value='';
 document.getElementById('smProjectType').value='Шкаф';
 document.getElementById('smProjectModalEyebrow').textContent='НОВЫЙ ПРОЕКТ';
 document.getElementById('smProjectModalTitle').textContent='Создать проект';
 document.getElementById('smProjectCreateConfirm').innerHTML='<i class="bi bi-plus-lg"></i> Создать проект';
 bootstrap.Modal.getOrCreateInstance(document.getElementById('smProjectModal')).show();
 setTimeout(()=>document.getElementById('smProjectName').focus(),300);
}
function editProject(id){
 const p=state.projects.find(x=>x.id===id);if(!p)return;
 document.getElementById('smProjectEditId').value=p.id;
 document.getElementById('smProjectName').value=p.name||'';
 document.getElementById('smProjectClient').value=p.client||'';
 document.getElementById('smProjectPhone').value=formatRuPhone(p.phone||'');
 document.getElementById('smProjectObject').value=p.objectName||'';
 const typeSelect=document.getElementById('smProjectType');const projectType=p.type||'Другое';
 if(!Array.from(typeSelect.options).some(o=>o.value===projectType)){typeSelect.add(new Option(projectType,projectType));}
 typeSelect.value=projectType;
 document.getElementById('smProjectModalEyebrow').textContent='РЕДАКТИРОВАНИЕ ПРОЕКТА';
 document.getElementById('smProjectModalTitle').textContent='Изменить проект';
 document.getElementById('smProjectCreateConfirm').innerHTML='<i class="bi bi-check2"></i> Сохранить изменения';
 bootstrap.Modal.getOrCreateInstance(document.getElementById('smProjectModal')).show();
 setTimeout(()=>document.getElementById('smProjectName').focus(),300);
}
function formatRuPhone(value){
 let digits=String(value||'').replace(/\\D/g,'');
 if(!digits)return '';
 if(digits[0]==='8')digits='7'+digits.slice(1);
 else if(digits[0]!=='7')digits='7'+digits;
 digits=digits.slice(0,11);
 const rest=digits.slice(1);
 let out='+7';
 if(rest.length)out+=' ('+rest.slice(0,3);
 if(rest.length>=3)out+=')';
 if(rest.length>3)out+=' '+rest.slice(3,6);
 if(rest.length>6)out+='-'+rest.slice(6,8);
 if(rest.length>8)out+='-'+rest.slice(8,10);
 return out;
}
function confirmCreateProject(){
 const name=document.getElementById('smProjectName').value.trim();
 if(!name){document.getElementById('smProjectName').focus();return;}
 const client=document.getElementById('smProjectClient').value.trim();
 const phone=formatRuPhone(document.getElementById('smProjectPhone').value.trim());
 const objectName=document.getElementById('smProjectObject').value.trim();
 const type=document.getElementById('smProjectType').value.trim()||'Строительство';
 const editId=document.getElementById('smProjectEditId').value;
 if(editId){
   const p=state.projects.find(x=>x.id===editId);if(!p)return;
   Object.assign(p,{name,client,phone,objectName,type,date:new Date().toLocaleDateString('ru-RU')});
   save();bootstrap.Modal.getInstance(document.getElementById('smProjectModal'))?.hide();renderProjects();return;
 }
 state.sections=[];state.winter=false;state.tight=false;state.custom=1;state.method='resource';
 const p={id:uid(),name,client,phone,objectName,type,date:new Date().toLocaleDateString('ru-RU'),sections:[],winter:false,tight:false,custom:1,method:'resource',stagesTotal:9,stagesDone:0};
 state.projects.unshift(p);state.currentProjectId=p.id;save();
 bootstrap.Modal.getInstance(document.getElementById('smProjectModal'))?.hide();
 openEstimate();
}
function deleteProject(id){
 const p=state.projects.find(x=>x.id===id);if(!p)return;
 showConfirm('Удалить проект?','Проект «'+p.name+'» и вся его смета будут удалены без возможности восстановления.','Удалить проект',()=>{
   state.projects=state.projects.filter(x=>x.id!==id);
   if(state.currentProjectId===id){state.currentProjectId=null;state.sections=[];state.winter=false;state.tight=false;state.custom=1;state.method='resource';}
   save();renderProjects();
 });
}
function openProject(id){
 const p=state.projects.find(x=>x.id===id);if(!p)return;
 state.currentProjectId=id;state.sections=JSON.parse(JSON.stringify(p.sections||[]));state.winter=!!p.winter;state.tight=!!p.tight;state.custom=num(p.custom)||1;state.method=p.method||'resource';save();openEstimate();
}
function openEstimate(){
 document.getElementById('smProjectsView').style.display='none';document.getElementById('smEstimateApp').style.display='block';
 document.getElementById('smWinter').checked=state.winter;document.getElementById('smTight').checked=state.tight;document.getElementById('smCustom').value=state.custom;document.getElementById('smMethod').value=state.method;render();
}
function openProjects(){if(state.currentProjectId){const p=state.projects.find(x=>x.id===state.currentProjectId);if(p){p.sections=snapshotCurrent().sections;p.winter=state.winter;p.tight=state.tight;p.custom=state.custom;p.method=state.method;}}save();document.getElementById('smEstimateApp').style.display='none';document.getElementById('smProjectsView').style.display='block';renderProjects();}

function load(){try{const x=JSON.parse(localStorage.getItem(KEY)||'null');if(x&&Array.isArray(x.sections)){state.sections=x.sections;state.winter=!!x.winter;state.tight=!!x.tight;state.custom=num(x.custom)||1;state.method=x.method||'resource';state.projects=Array.isArray(x.projects)?x.projects:[];state.currentProjectId=x.currentProjectId||null;
if(!state.projects.length && state.sections.length){const p={id:uid(),name:'Мой проект',date:new Date().toLocaleDateString('ru-RU'),sections:JSON.parse(JSON.stringify(state.sections)),winter:state.winter,tight:state.tight,custom:state.custom,method:state.method};state.projects=[p];state.currentProjectId=p.id;}
}}
catch(e){}}
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
function addSection(){const s=section();state.sections.push(s);render();setTimeout(()=>editSection(s.id),0);}
function editSection(id){
 const s=state.sections.find(x=>x.id===id);if(!s)return;
 document.getElementById('smSectionId').value=id;
 document.getElementById('smSectionName').value=s.name;
 document.getElementById('smSectionSave').dataset.mode='edit';
 bootstrap.Modal.getOrCreateInstance(document.getElementById('smSectionModal')).show();
 setTimeout(()=>document.getElementById('smSectionName').focus(),250);
}
function openManualEntry(sid,iid=null){
 const s=state.sections.find(x=>x.id===sid);if(!s)return;
 const it=iid?s.items.find(x=>x.id===iid):null;
 document.getElementById('smEntrySid').value=sid;
 document.getElementById('smEntryIid').value=iid||'';
 document.getElementById('smEntryName').value=it?.name||'';
 document.getElementById('smEntryQty').value=it?.quantity||1;
 document.getElementById('smEntryUnit').value=it?.unit||'шт';
 document.getElementById('smEntryPrice').value=it?.price||0;
 document.getElementById('smEntryEyebrow').textContent=it?'РЕДАКТИРОВАНИЕ':'РУЧНОЙ ВВОД';
 document.getElementById('smEntryTitle').textContent=it?'Изменить позицию':'Добавить вручную';
 document.getElementById('smEntryHint').textContent=it?'Измените нужные параметры позиции.':'Добавьте работу, материал или услугу без каталога.';
 document.getElementById('smEntrySave').innerHTML=it?'<i class="bi bi-check2"></i> Сохранить':'<i class="bi bi-plus-lg"></i> Добавить в смету';
 bootstrap.Modal.getOrCreateInstance(document.getElementById('smEntryModal')).show();
 setTimeout(()=>document.getElementById('smEntryName').focus(),250);
}
function saveManualEntry(){
 const sid=document.getElementById('smEntrySid').value,iid=document.getElementById('smEntryIid').value;
 const s=state.sections.find(x=>x.id===sid);if(!s)return;
 const name=document.getElementById('smEntryName').value.trim();
 if(!name){document.getElementById('smEntryName').focus();return;}
 const quantity=Math.max(.001,num(document.getElementById('smEntryQty').value)||1);
 const unit=document.getElementById('smEntryUnit').value.trim()||'шт';
 const price=Math.max(0,num(document.getElementById('smEntryPrice').value));
 if(iid){const it=s.items.find(x=>x.id===iid);if(it){it.name=name;it.quantity=quantity;it.unit=unit;it.price=price;}}
 else{s.items.push(item(name,unit,price,quantity));}
 bootstrap.Modal.getInstance(document.getElementById('smEntryModal'))?.hide();render();
}
let selected={sid:null,data:null};
 let lastSectionId=null;
function openCatalog(sid){selected.sid=sid;lastSectionId=sid;selected.data=null;document.getElementById('smSelected').hidden=true;document.getElementById('smCatalogAdd').disabled=true;document.getElementById('smCatalogSearch').value='';filterCatalog();bootstrap.Modal.getOrCreateInstance(document.getElementById('smCatalogModal')).show();setTimeout(()=>document.getElementById('smCatalogSearch').focus(),300);}
function openToolbarCatalog(){let sid=lastSectionId&&state.sections.some(s=>s.id===lastSectionId)?lastSectionId:(state.sections[0]?.id||null);if(!sid){const s=section();state.sections.push(s);render();sid=s.id;}openCatalog(sid);}
 function filterCatalog(){const q=document.getElementById('smCatalogSearch').value.toLowerCase();document.querySelectorAll('.sm-catalog-item').forEach(x=>x.hidden=!!q&&!x.dataset.name.includes(q));}
function addSelected(){if(templateBuilderMode){finishCustomTemplateBuilder();return;}if(!selected.sid||!selected.data)return;const s=state.sections.find(x=>x.id===selected.sid);if(!s)return;const q=num(document.getElementById('smSelectedQty').value)||1;s.items.push(item(selected.data.title,selected.data.unit,selected.data.price,q));bootstrap.Modal.getInstance(document.getElementById('smCatalogModal'))?.hide();render();}
function renderCustomTemplates(){
 const grid=document.getElementById('smCustomTemplates'),empty=document.getElementById('smCustomTemplatesEmpty');if(!grid||!empty)return;
 grid.innerHTML=customTemplates.map(t=>{
  const count=(t.sections||[]).reduce((n,s)=>n+(s.items||[]).length,0);
  const description=(t.sections||[]).map(s=>s.name).join(' · ')||'Пользовательский шаблон';
  return '<div class="sm-custom-template-card"><button type="button" class="sm-template" data-custom-template-use="'+esc(t.id)+'"><span class="sm-template-icon"><i class="bi bi-bookmark-star"></i></span><strong>'+esc(t.name)+'</strong><small>'+esc(description)+'</small><b>'+count+' позиций</b></button><button type="button" class="sm-template-delete" data-custom-template-delete="'+esc(t.id)+'" aria-label="Удалить шаблон" title="Удалить шаблон"><i class="bi bi-trash"></i></button></div>';
 }).join('');
 empty.hidden=customTemplates.length>0;
}
function saveCustomTemplates(){localStorage.setItem(CUSTOM_TEMPLATES_KEY,JSON.stringify(customTemplates));renderCustomTemplates();}
let templateBuilderMode=false,templateBuilderDraft=null;
const templateBuilderSelection=new Map();
function openSaveTemplate(){
 document.getElementById('smTemplateName').value='';
 document.getElementById('smTemplateSaveError').hidden=true;
 document.getElementById('smSaveTemplateConfirm').disabled=false;
 const open=()=>{bootstrap.Modal.getOrCreateInstance(document.getElementById('smSaveTemplateModal')).show();setTimeout(()=>document.getElementById('smTemplateName').focus(),250);};
 const templatesModal=bootstrap.Modal.getInstance(document.getElementById('smTemplateModal'));
 if(templatesModal){templatesModal.hide();setTimeout(open,250);}else open();
}
function saveCustomTemplate(){
 const name=document.getElementById('smTemplateName').value.trim();
 if(!name){document.getElementById('smTemplateName').focus();return;}
 templateBuilderDraft={name};
 templateBuilderSelection.clear();
 document.getElementById('smTemplateSaveError').hidden=true;
 bootstrap.Modal.getInstance(document.getElementById('smSaveTemplateModal'))?.hide();
 setTimeout(openTemplateBuilderCatalog,250);
}
function openTemplateBuilderCatalog(){
 templateBuilderMode=true;selected.sid=null;selected.data=null;
 document.querySelectorAll('.sm-catalog-item.selected').forEach(x=>x.classList.remove('selected'));
 document.getElementById('smCatalogSearch').value='';
 document.getElementById('smSelected').hidden=true;
 document.getElementById('smCatalogEyebrow').textContent='СОЗДАНИЕ ШАБЛОНА';
 document.getElementById('smCatalogTitle').textContent='Выберите позиции для шаблона';
 document.getElementById('smCatalogSubtitle').textContent='Шаблон: «'+templateBuilderDraft.name+'». Отметьте нужные материалы и фурнитуру.';
 document.getElementById('smCatalogHint').innerHTML='<i class="bi bi-check2-square"></i> Можно выбрать несколько позиций. Нажмите «Готово», когда закончите.';
 document.getElementById('smCatalogAdd').disabled=true;
 document.getElementById('smCatalogAdd').innerHTML='<i class="bi bi-check2-circle"></i> Готово · сохранить шаблон';
 filterCatalog();
 bootstrap.Modal.getOrCreateInstance(document.getElementById('smCatalogModal')).show();
}
function finishCustomTemplateBuilder(){
 if(!templateBuilderDraft||!templateBuilderSelection.size)return;
 const groups=new Map();
 templateBuilderSelection.forEach(i=>{
  const category=i.category||'Материалы и фурнитура';
  if(!groups.has(category))groups.set(category,[]);
  groups.get(category).push({name:i.name,unit:i.unit,price:num(i.price),quantity:1});
 });
 const sections=Array.from(groups,([name,items])=>({name,items}));
 customTemplates.unshift({id:uid(),name:templateBuilderDraft.name,sections,createdAt:new Date().toISOString()});
 saveCustomTemplates();
 templateBuilderDraft=null;templateBuilderSelection.clear();templateBuilderMode=false;selected.sid=null;selected.data=null;
 bootstrap.Modal.getInstance(document.getElementById('smCatalogModal'))?.hide();
 document.getElementById('smCatalogEyebrow').textContent='КАТАЛОГ';
 document.getElementById('smCatalogTitle').textContent='Добавить работу или материал';
 document.getElementById('smCatalogSubtitle').textContent='Выберите позицию и добавьте её в нужный раздел.';
 document.getElementById('smCatalogHint').innerHTML='<i class="bi bi-mouse2"></i> Двойной клик по позиции — сразу добавить в смету';
 document.getElementById('smCatalogAdd').innerHTML='<i class="bi bi-plus-lg"></i> Добавить в смету';
 setTimeout(()=>bootstrap.Modal.getOrCreateInstance(document.getElementById('smTemplateModal')).show(),250);
}
function toggleTemplateBuilderItem(button){
 const id=String(button.dataset.id||'');
 if(!id)return;
 if(templateBuilderSelection.has(id)){templateBuilderSelection.delete(id);button.classList.remove('selected');}
 else{
  templateBuilderSelection.set(id,{id,name:button.dataset.title,unit:button.dataset.unit,price:num(button.dataset.price),category:button.dataset.category});
  button.classList.add('selected');
 }
 document.getElementById('smCatalogAdd').disabled=templateBuilderSelection.size===0;
 document.getElementById('smCatalogAdd').innerHTML='<i class="bi bi-check2-circle"></i> Готово · сохранить шаблон ('+templateBuilderSelection.size+')';
}
function applyCustomTemplate(id){
 const t=customTemplates.find(x=>x.id===id);if(!t)return;
 (t.sections||[]).forEach(saved=>{const s=section(saved.name);(saved.items||[]).forEach(i=>s.items.push(item(i.name,i.unit,i.price,i.quantity||1)));state.sections.push(s);});
 bootstrap.Modal.getInstance(document.getElementById('smTemplateModal'))?.hide();render();
}
function applyTemplate(type){const data=templates[type];if(!data)return;data.forEach(([name,rows])=>{const s=section(name);rows.forEach(([n,u,p])=>s.items.push(item(n,u,p,1)));state.sections.push(s)});bootstrap.Modal.getInstance(document.getElementById('smTemplateModal'))?.hide();render();}
function showConfirm(title,text,ok,action){document.getElementById('smConfirmTitle').textContent=title;document.getElementById('smConfirmText').textContent=text;const b=document.getElementById('smConfirmOk');b.textContent=ok;b.onclick=()=>{bootstrap.Modal.getInstance(document.getElementById('smConfirmModal'))?.hide();action();};bootstrap.Modal.getOrCreateInstance(document.getElementById('smConfirmModal')).show();}
function exportPrint(){window.print();}
function loadSaved(){load();document.getElementById('smWinter').checked=state.winter;document.getElementById('smTight').checked=state.tight;document.getElementById('smCustom').value=state.custom;document.getElementById('smMethod').value=state.method;render();}
function editEstimateRow(row){
 if(!row||row.classList.contains('editing'))return;
 const sid=row.closest('.sm-group')?.dataset.sid;
 const s=state.sections.find(x=>x.id===sid);
 const it=s?.items.find(x=>x.id===row.dataset.iid);
 if(!s||!it)return;
 row.classList.add('editing');
 row.innerHTML='<div class="sm-work"><i class="sm-dot"></i><input class="sm-cell-input js-edit-name" aria-label="Наименование" value="'+esc(it.name)+'"></div><div><input class="sm-cell-input js-edit-qty" aria-label="Количество" type="number" min=".001" step=".001" value="'+esc(String(it.quantity))+'"></div><div><input class="sm-cell-input js-edit-unit" aria-label="Единица измерения" value="'+esc(it.unit)+'"></div><div><input class="sm-cell-input js-edit-price" aria-label="Цена" type="number" min="0" step=".01" value="'+esc(String(it.price))+'"></div><strong class="js-edit-sum">—</strong><div class="sm-actions"><button type="button" data-action="save-inline-item" title="Сохранить"><i class="bi bi-check2"></i></button><button type="button" data-action="cancel-inline-item" title="Отменить"><i class="bi bi-x-lg"></i></button></div>';
 row.querySelector('.js-edit-name')?.focus();
 row.querySelector('.js-edit-name')?.select();
}
function saveEstimateRow(row){
 if(!row)return;
 const sid=row.closest('.sm-group')?.dataset.sid;
 const s=state.sections.find(x=>x.id===sid);
 const it=s?.items.find(x=>x.id===row.dataset.iid);
 if(!s||!it)return;
 const name=row.querySelector('.js-edit-name')?.value.trim()||'';
 const quantity=num(row.querySelector('.js-edit-qty')?.value);
 const unit=row.querySelector('.js-edit-unit')?.value.trim()||'шт';
 const price=num(row.querySelector('.js-edit-price')?.value);
 if(!name){row.querySelector('.js-edit-name')?.focus();return;}
 if(quantity<=0){row.querySelector('.js-edit-qty')?.focus();return;}
 if(price<0){row.querySelector('.js-edit-price')?.focus();return;}
 Object.assign(it,{name,quantity,unit,price});
 render();
}
document.addEventListener('dblclick',e=>{const row=e.target.closest('.sm-row');if(!row||e.target.closest('.sm-actions'))return;if(!row.dataset.iid)return;editEstimateRow(row);});
document.addEventListener('click',e=>{
 const t=e.target.closest('[data-action]');if(!t)return;
 const g=t.closest('.sm-group'),sid=t.dataset.sid||g?.dataset.sid,action=t.dataset.action;
 if(action==='delete-section'){showConfirm('Удалить раздел?','Раздел и все его позиции будут удалены.','Удалить',()=>{state.sections=state.sections.filter(s=>s.id!==sid);render();});}
 if(action==='catalog')openCatalog(sid);
 if(action==='manual')openManualEntry(sid);
 if(action==='edit-item'){const row=t.closest('.sm-row');editEstimateRow(row);}
 if(action==='save-inline-item'){saveEstimateRow(t.closest('.sm-row'));}
 if(action==='cancel-inline-item'){render();}
 if(action==='delete-item'){const row=t.closest('.sm-row');const s=state.sections.find(x=>x.id===sid);if(s)showConfirm('Удалить позицию?','Позиция будет удалена из текущей сметы.','Удалить',()=>{s.items=s.items.filter(i=>i.id!==row.dataset.iid);render();});}
});
document.getElementById('smSectionSave').onclick=()=>{const id=document.getElementById('smSectionId').value,name=document.getElementById('smSectionName').value.trim();if(!name){document.getElementById('smSectionName').focus();return;}const s=state.sections.find(x=>x.id===id);if(s)s.name=name;bootstrap.Modal.getInstance(document.getElementById('smSectionModal'))?.hide();render();};
document.getElementById('smEntrySave').onclick=saveManualEntry;
document.getElementById('smNewEstimate').onclick=createProject;

document.getElementById('smProjectCreateConfirm').onclick=confirmCreateProject;
document.getElementById('smProjectPhone').addEventListener('input',e=>{const pos=e.target.selectionStart;e.target.value=formatRuPhone(e.target.value);try{e.target.setSelectionRange(e.target.value.length,e.target.value.length);}catch(err){}});
document.getElementById('smStageSave').onclick=()=>{const id=document.getElementById('smStageProjectId').value;const p=state.projects.find(x=>x.id===id);if(!p)return;p.stagesDone=Math.min(Number(p.stagesTotal)||9,Math.max(0,Math.round(num(document.getElementById('smStageValue').value))));save();bootstrap.Modal.getInstance(document.getElementById('smStageModal'))?.hide();renderProjects();};
document.getElementById('smCreateProject').onclick=createProject;
document.getElementById('smBackProjects').onclick=openProjects;
document.getElementById('smProjectSearch').oninput=renderProjects;
document.getElementById('smAddSection').onclick=addSection;
 document.getElementById('smCatalogToolbar').onclick=openToolbarCatalog;
document.getElementById('smEmptyAdd')?.addEventListener('click',addSection);
document.getElementById('smClear').onclick=()=>showConfirm('Очистить смету?','Все разделы и позиции текущего проекта будут удалены.','Очистить',()=>{state.sections=[];state.winter=false;state.tight=false;state.custom=1;render();document.getElementById('smWinter').checked=false;document.getElementById('smTight').checked=false;document.getElementById('smCustom').value=1;});
document.getElementById('smPrint').onclick=exportPrint;
document.getElementById('smSave').onclick=()=>{if(state.currentProjectId){const p=state.projects.find(x=>x.id===state.currentProjectId);if(p){p.sections=snapshotCurrent().sections;p.winter=state.winter;p.tight=state.tight;p.custom=state.custom;p.method=state.method;p.date=new Date().toLocaleDateString('ru-RU');}}save();const b=document.getElementById('smSave');const old=b.innerHTML;b.innerHTML='<i class="bi bi-check2"></i> Сохранено';setTimeout(()=>b.innerHTML=old,1300);};
document.getElementById('smWinter').onchange=e=>{state.winter=e.target.checked;render()};
document.getElementById('smTight').onchange=e=>{state.tight=e.target.checked;render()};
document.getElementById('smCustom').oninput=e=>{state.custom=Math.max(.1,num(e.target.value)||1);render()};
document.getElementById('smMethod').onchange=e=>{state.method=e.target.value;render()};
document.getElementById('smCatalogSearch').oninput=filterCatalog;
document.getElementById('smCatalogGrid').onclick=e=>{const b=e.target.closest('.sm-catalog-item');if(!b)return;if(templateBuilderMode){toggleTemplateBuilderItem(b);return;}document.querySelectorAll('.sm-catalog-item.selected').forEach(x=>x.classList.remove('selected'));b.classList.add('selected');selected.data=b.dataset;document.getElementById('smSelectedName').textContent=b.dataset.title;document.getElementById('smSelected').hidden=false;document.getElementById('smCatalogAdd').disabled=false;};
document.getElementById('smCatalogGrid').ondblclick=e=>{const b=e.target.closest('.sm-catalog-item');if(!b)return;if(templateBuilderMode){if(!templateBuilderSelection.has(String(b.dataset.id||'')))toggleTemplateBuilderItem(b);return;}if(!selected.sid||!state.sections.some(x=>x.id===selected.sid))return;const s=state.sections.find(x=>x.id===selected.sid);if(!s)return;selected.data=b.dataset;s.items.push(item(b.dataset.title,b.dataset.unit,b.dataset.price,1));bootstrap.Modal.getInstance(document.getElementById('smCatalogModal'))?.hide();render();};
document.getElementById('smCatalogAdd').onclick=addSelected;
document.getElementById('smTemplates').onclick=e=>{const b=e.target.closest('[data-template]');if(b)applyTemplate(b.dataset.template)};
document.getElementById('smSaveTemplateOpen').onclick=openSaveTemplate;
document.getElementById('smSaveTemplateConfirm').onclick=saveCustomTemplate;
document.getElementById('smTemplateName').addEventListener('keydown',e=>{if(e.key==='Enter')saveCustomTemplate();});
document.getElementById('smCustomTemplates').onclick=e=>{
 const del=e.target.closest('[data-custom-template-delete]');
 if(del){e.stopPropagation();const id=del.dataset.customTemplateDelete;const t=customTemplates.find(x=>x.id===id);if(t)showConfirm('Удалить шаблон?','Шаблон «'+t.name+'» будет удалён.','Удалить',()=>{customTemplates=customTemplates.filter(x=>x.id!==id);saveCustomTemplates();});return;}
 const use=e.target.closest('[data-custom-template-use]');if(use)applyCustomTemplate(use.dataset.customTemplateUse);
};
document.addEventListener('click',e=>{if(e.target.closest('#smEmptyAdd'))addSection();if(e.target.closest('#smEmptyProject'))createProject();});
loadSaved();
renderCustomTemplates();
document.getElementById('smProjectsView').style.display='block';
document.getElementById('smEstimateApp').style.display='none';
renderProjects();
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php';