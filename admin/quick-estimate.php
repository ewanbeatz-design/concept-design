<?php
$pageTitle='Быстрая смета';
require_once __DIR__.'/includes/header.php';
if(!$pdo){echo '<div class="admin-card"><h2 class="admin-card-title">Нет подключения к БД</h2></div>';require_once __DIR__.'/includes/footer.php';exit;}
$pdo->exec("CREATE TABLE IF NOT EXISTS quick_estimate_catalog(
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, category VARCHAR(40) NOT NULL, name VARCHAR(255) NOT NULL,
brand VARCHAR(100) NULL, article VARCHAR(120) NULL, unit VARCHAR(20) NOT NULL DEFAULT 'шт',
price DECIMAL(12,2) NOT NULL DEFAULT 0, active TINYINT(1) NOT NULL DEFAULT 1,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
KEY category(category),KEY active(active)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
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
if(!(int)$pdo->query("SELECT COUNT(*) FROM quick_estimate_catalog")->fetchColumn()){
 $s=$pdo->prepare("INSERT INTO quick_estimate_catalog(category,name,brand,article,unit,price) VALUES(?,?,?,?,?,?)");
 foreach($seed as $x)$s->execute($x);
}
$cat=$pdo->query("SELECT * FROM quick_estimate_catalog WHERE active=1 ORDER BY FIELD(category,'materials','countertop','hardware','fasteners'),name")->fetchAll();
$labels=['materials'=>'ЛДСП / МДФ / Кромка','countertop'=>'Столешницы','hardware'=>'Фурнитура','fasteners'=>'Крепёж и расходники'];
function qem($n){return number_format((float)$n,2,',',' ').' ₽';}
?>
<div class="qe-page">
 <div class="qe-head"><div><div class="qe-eyebrow">CONCEPT / CALCULATOR</div><h2>Быстрая смета корпусной мебели</h2><p>Собирайте состав изделия из каталога и сразу считайте стоимость материалов и комплектующих.</p></div><button class="admin-btn" id="qeClear"><i class="bi bi-arrow-counterclockwise"></i> Очистить</button></div>
 <div class="qe-layout">
  <section class="qe-catalog">
   <div class="qe-toolbar"><div class="qe-search"><i class="bi bi-search"></i><input id="qeSearch" placeholder="Поиск материала, артикула, бренда..."></div>
    <div class="qe-tabs" id="qeTabs"><button class="active" data-cat="all">Все</button><?php foreach($labels as $k=>$v):?><button data-cat="<?=$k?>"><?=h($v)?></button><?php endforeach;?></div>
   </div>
   <div class="qe-grid"><?php foreach($cat as $x):?><article class="qe-product" data-cat="<?=h($x['category'])?>" data-search="<?=h(mb_strtolower($x['name'].' '.$x['brand'].' '.$x['article']))?>">
    <div class="qe-product-top"><span><?=h($labels[$x['category']])?></span><b><?=h($x['unit'])?></b></div><h3><?=h($x['name'])?></h3><small><?=h($x['brand'])?> · <?=h($x['article'])?></small>
    <div class="qe-product-bottom"><strong><?=qem($x['price'])?></strong><button class="qe-add" data-id="<?=$x['id']?>" data-name="<?=h($x['name'])?>" data-brand="<?=h($x['brand'])?>" data-unit="<?=h($x['unit'])?>" data-price="<?=$x['price']?>"><i class="bi bi-plus-lg"></i> Добавить</button></div>
   </article><?php endforeach;?></div>
  </section>
  <aside class="qe-estimate"><div class="qe-estimate-head"><div><div class="qe-eyebrow">РАСЧЁТ</div><h3>Состав сметы</h3></div><span id="qeCount">0 позиций</span></div>
   <div id="qeItems"><div class="qe-empty"><i class="bi bi-calculator"></i><b>Смета пустая</b><span>Добавьте позиции из каталога.</span></div></div>
   <div class="qe-summary"><div><span>Себестоимость</span><b id="qeSub">0 ₽</b></div><div><span>Наценка</span><label><input id="qeMarkup" type="number" value="0" min="0" max="100"> %</label></div><div class="qe-total"><span>Итого</span><strong id="qeTotal">0 ₽</strong></div></div>
   <button class="qe-save" onclick="window.print()"><i class="bi bi-printer"></i> Печать / PDF</button>
  </aside>
 </div>
</div>
<script>
(()=>{const S={a:[],m:0},M=n=>new Intl.NumberFormat('ru-RU',{maximumFractionDigits:2}).format(n)+' ₽';
const esc=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
function draw(){let box=document.getElementById('qeItems'),sum=0;box.querySelectorAll('.qe-line,.qe-empty').forEach(e=>e.remove());S.a.forEach((x,i)=>{let t=x.p*x.q;sum+=t;let d=document.createElement('div');d.className='qe-line';d.innerHTML='<div><b>'+esc(x.n)+'</b><small>'+esc(x.b)+' · '+esc(x.u)+'</small></div><div class="qe-qty"><button data-a="m" data-i="'+i+'">−</button><input data-a="q" data-i="'+i+'" value="'+x.q+'"><button data-a="p" data-i="'+i+'">+</button></div><strong>'+M(t)+'</strong><button data-a="d" data-i="'+i+'" class="qe-remove">×</button>';box.appendChild(d)});if(!S.a.length)box.innerHTML='<div class="qe-empty"><i class="bi bi-calculator"></i><b>Смета пустая</b><span>Добавьте позиции из каталога.</span></div>';let total=sum+sum*S.m/100;document.getElementById('qeSub').textContent=M(sum);document.getElementById('qeTotal').textContent=M(total);document.getElementById('qeCount').textContent=S.a.length+' поз.'}
document.querySelectorAll('.qe-add').forEach(b=>b.onclick=()=>{let id=+b.dataset.id,x=S.a.find(z=>z.id===id);x?x.q++:S.a.push({id,n:b.dataset.name,b:b.dataset.brand,u:b.dataset.unit,p:+b.dataset.price,q:1});draw()});
document.getElementById('qeItems').onclick=e=>{let b=e.target.closest('[data-a]');if(!b)return;let i=+b.dataset.i;if(b.dataset.a==='p')S.a[i].q++;if(b.dataset.a==='m')S.a[i].q=Math.max(.01,S.a[i].q-1);if(b.dataset.a==='d')S.a.splice(i,1);draw()};
document.getElementById('qeItems').onchange=e=>{if(e.target.dataset.a==='q'){S.a[+e.target.dataset.i].q=Math.max(.01,+e.target.value||1);draw()}};
document.getElementById('qeMarkup').oninput=e=>{S.m=Math.max(0,+e.target.value||0);draw()};
document.getElementById('qeClear').onclick=()=>{S.a=[];S.m=0;document.getElementById('qeMarkup').value=0;draw()};
function filter(){let q=document.getElementById('qeSearch').value.toLowerCase(),c=document.querySelector('.qe-tabs .active').dataset.cat;document.querySelectorAll('.qe-product').forEach(x=>x.style.display=(c==='all'||x.dataset.cat===c)&&(!q||x.dataset.search.includes(q))?'':'none')}
document.getElementById('qeSearch').oninput=filter;document.getElementById('qeTabs').onclick=e=>{let b=e.target.closest('button');if(!b)return;document.querySelectorAll('#qeTabs button').forEach(x=>x.classList.remove('active'));b.classList.add('active');filter()};draw()})();
</script>
<?php require_once __DIR__.'/includes/footer.php';