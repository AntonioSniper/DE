<?php
$pageTitle = 'Панель администратора';
require __DIR__ . '/src/bootstrap.php';
requireUser();
if ((int)$user['role_id'] !== 1) errorPage(403, 'Доступ запрещён');
$type = max(0, (int)($_GET['type'] ?? 0));
$status = max(0, (int)($_GET['status'] ?? 0));
if (posted('status')) {
    $newStatus = filter_var($_POST['status_id'] ?? '', FILTER_VALIDATE_INT);
    $id = filter_var($_POST['application_id'] ?? '', FILTER_VALIDATE_INT);
    if (!in_array($newStatus, [2, 3], true)) {
        $errors['general'] = 'Выберите корректный статус.';
    } elseif (!$repo->query('UPDATE applications SET status_id=? WHERE application_id=?', [$newStatus, $id])->rowCount()) {
        http_response_code(404);
        $errors['general'] = 'Заявка не найдена.';
    } else {
        flash('Статус заявки сохранён.');
        go('admin.php', ['type'=>$type, 'status'=>$status, 'p'=>max(1,(int)($_GET['p'] ?? 1))]);
    }
}
$statuses = $repo->query('SELECT * FROM statuses ORDER BY status_id')->fetchAll();

$admin = true;
require __DIR__ . '/src/applications.php';
require __DIR__ . '/src/header.php';
?>
<div class="list-heading">
<div>
<p class="eyebrow">ПЕДАГОГ-ОРГАНИЗАТОР</p>
<h1><?=e($pageTitle)?></h1></div></div>
<form class="filters" method="get">
<label for="type">Тип мероприятия<select name="type" id="type">
<option value="0">Все типы</option><?php foreach($repo->query('SELECT * FROM event_types') as $t):?>
<option value="<?=$t['type_id']?>" <?=$type===(int)$t['type_id']?'selected':''?>><?=e($t['type_name'])?></option><?php endforeach?></select></label>
<label for="status">Статус<select name="status" id="status">
<option value="0">Все статусы</option><?php foreach($statuses as $s):?>
<option value="<?=$s['status_id']?>" <?=$status===(int)$s['status_id']?'selected':''?>><?=e($s['status_name'])?></option><?php endforeach?></select></label>
<button class="button">Показать</button></form>
<p class="result-count">Найдено заявок: <?=$total?></p>
<?php if(!$items):?>
<section class="empty"><span aria-hidden="true">↗</span>
<h2>Заявки не найдены</h2>
<p>Измените фильтры или дождитесь новых регистраций.</p></section><?php endif?>
<div class="cards">
<?php foreach ($items as $a): ?>
<?php require __DIR__ . '/src/application-card.php'; ?>
<form method="post" class="card-form"><?=csrf()?>
<input type="hidden" name="application_id" value="<?=$a['application_id']?>">
<label for="status-<?=$a['application_id']?>">Статус участия<select id="status-<?=$a['application_id']?>" name="status_id"><?php foreach($statuses as $s):?>
<option value="<?=$s['status_id']?>" <?=(int)$s['status_id'] === 1 ? 'disabled' : ''?> <?=(int)$s['status_id']===(int)$a['status_id']?'selected':''?>><?=e($s['status_name'])?></option><?php endforeach?></select></label>
<button name="action" value="status" class="button full">Сохранить статус</button></form>
</article>
<?php endforeach ?>
</div>
<?php require __DIR__ . '/src/pagination.php'; ?>
<?php require __DIR__ . '/src/footer.php';
