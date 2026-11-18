<?php
$pageTitle = 'Внеучебная жизнь техникума';
require __DIR__ . '/src/bootstrap.php';
if (isset($_GET['page'])) {
    $files = ['home'=>'index.php', 'register'=>'register.php', 'login'=>'login.php',
              'apply'=>'apply.php', 'my'=>'my.php', 'admin'=>'admin.php'];
    $file = $files[$_GET['page']] ?? null;
    if (!$file) errorPage(404, 'Страница не найдена');
    $params = $_GET;
    unset($params['page']);
    $query = http_build_query($params);
    header('Location: ' . $file . ($query === '' ? '' : '?' . $query), true, 307);
    exit;
}
require __DIR__ . '/src/header.php';
?>
<section class="intro">
<p class="eyebrow">ПОРТАЛ ДЛЯ СТУДЕНТОВ</p>
<h1>Учёба объединяет.<br><em>Участие открывает новое.</em></h1>
<p>Олимпиады, конкурсы, экскурсии, мастер-классы и конференции. Выбирайте событие и отправляйте заявку вместе со своей командой.</p><a class="button" href="<?=$user?((int)$user['role_id']===1?'admin.php':'apply.php'):'register.php'?>"><?=$user?((int)$user['role_id']===1?'Панель администратора':'Подать заявку'):'Зарегистрироваться'?> <span aria-hidden="true">↗</span></a></section>
<section class="slider-wrap" aria-label="Мероприятия техникума" aria-roledescription="карусель">
<div class="slider">
<?php $captions=['Олимпиады и командная работа','Экскурсии и новые впечатления','Студенческая наука и конференции'];foreach($captions as $i=>$caption):?>
<figure class="slide <?=$i===0?'active':''?>"><img src="assets/slide-<?=$i+1?>.webp" alt="<?=e($caption)?>" width="1280" height="720"><figcaption><span>0<?=$i+1?> / 03</span><strong><?=e($caption)?></strong></figcaption></figure><?php endforeach?>
</div>
<div class="slide-controls">
<button type="button" data-slide="-1" aria-label="Предыдущий слайд">←</button>
<div class="dots"><?php for($i=0;$i<3;$i++):?>
<button type="button" data-goto="<?=$i?>" aria-label="Слайд <?=$i+1?>" class="<?=$i===0?'active':''?>"></button><?php endfor?></div>
<button type="button" id="pause" aria-label="Приостановить слайдер">Ⅱ</button>
<button type="button" data-slide="1" aria-label="Следующий слайд">→</button></div></section>
<?php require __DIR__ . '/src/footer.php';
