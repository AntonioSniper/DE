<?php
$pageTitle = 'Мои регистрации';
require __DIR__ . '/src/bootstrap.php';
requireUser();
if (posted('review')) {
    $text = trim($_POST['review_text'] ?? '');
    $id = (int)filter_var($_POST['application_id'] ?? '', FILTER_VALIDATE_INT);
    if ($text === '' || strlen($text) > 65535) {
        $errors['general'] = 'Введите текст отзыва (до 65 535 байт).';
    } elseif (!$repo->review($id, (int)$user['user_id'], $text)) {
        http_response_code(403);
        $errors['general'] = 'Отзыв доступен владельцу один раз после подтверждённого участия.';
    } else {
        flash('Спасибо за отзыв!');
        go('my.php', ['p' => max(1, (int)($_POST['p'] ?? 1))]);
    }
}
$type = $status = 0;
$admin = false;
require __DIR__ . '/src/applications.php';
require __DIR__ . '/src/header.php';
?>
<div class="list-heading">
<div>
<p class="eyebrow">ЛИЧНЫЙ КАБИНЕТ</p>
<h1><?=e($pageTitle)?></h1></div><a class="button" href="apply.php">Подать заявку <span aria-hidden="true">+</span></a></div>
<?php if(!$items):?>
<section class="empty"><span aria-hidden="true">↗</span>
<h2>Вы ещё не подавали заявки</h2>
<p>Выберите мероприятие и отправьте первую заявку на участие.</p><a class="button" href="apply.php">Выбрать мероприятие</a></section><?php endif?>
<div class="cards">
<?php foreach ($items as $a): ?>
<?php require __DIR__ . '/src/application-card.php'; ?>
<?php if($a['review_id']!==null):?>
<div class="review"><strong>Ваш отзыв</strong>
<p><?=nl2br(e($a['review_text']))?></p></div>
<?php elseif((int)$a['status_id']===3):?>
<form method="post" class="card-form"><?=csrf()?>
<input type="hidden" name="application_id" value="<?=$a['application_id']?>">
<input type="hidden" name="p" value="<?=$current?>">
<label for="review-<?=$a['application_id']?>">Отзыв о мероприятии<textarea id="review-<?=$a['application_id']?>" name="review_text" rows="3" required></textarea></label>
<button class="button full" name="action" value="review">Оставить отзыв</button></form><?php endif?>
</article>
<?php endforeach ?>
</div>
<?php require __DIR__ . '/src/pagination.php'; ?>
<?php require __DIR__ . '/src/footer.php';
