<?php
$pageTitle = 'Заявка на участие';
require __DIR__ . '/src/bootstrap.php';
requireUser();
if (posted('apply')) {
    $eventId = filter_var($_POST['event_id'] ?? '', FILTER_VALIDATE_INT);
    $rawDate = trim($_POST['event_date'] ?? '');
    $date = DateTimeImmutable::createFromFormat('!d.m.Y', $rawDate);
    $phone = trim($_POST['contact_phone'] ?? '');
    $email = trim($_POST['contact_email'] ?? '');
    $members = array_filter(array_map('trim', preg_split('/\R/u', $_POST['team'] ?? '')), 'strlen');

    if (!$eventId || !$repo->query('SELECT event_id FROM events WHERE event_id=?', [$eventId])->fetchColumn()) {
        $errors['event_id'] = 'Выберите мероприятие из списка.';
    }
    if (!$date || $date->format('d.m.Y') !== $rawDate || (int)$date->format('Y') < 1000) {
        $errors['event_date'] = 'Укажите существующую дату в формате ДД.ММ.ГГГГ.';
    }
    if (!$members || array_filter($members, fn($name) => !nameValid($name))) {
        $errors['team'] = 'Укажите ФИО участников кириллицей, каждого с новой строки.';
    }
    if (!phoneValid($phone)) $errors['contact_phone'] = 'Формат: 8(999)123-45-67.';
    if (!emailValid($email)) $errors['contact_email'] = 'Введите корректный email.';

    if (!$errors) {
        $repo->createApplication((int)$user['user_id'], $eventId, $date->format('Y-m-d'), $phone, $email, $members);
        flash('Заявка направлена педагогу-организатору.');
        go('my.php');
    }
}
require __DIR__ . '/src/header.php';
?>
<div class="form-heading">
<p class="eyebrow">ВАША КОМАНДА — В ЦЕНТРЕ СОБЫТИЙ</p>
<h1><?=e($pageTitle)?></h1></div>
<section class="form-card">
<form method="post"><?=csrf()?>
<div class="field">
<label for="event_id">Наименование мероприятия</label>
<select id="event_id" name="event_id" required>
<option value="">Выберите мероприятие</option><?php foreach($repo->events() as $event):?>
<option value="<?=$event['event_id']?>" <?=(int)($_POST['event_id']??0)===(int)$event['event_id']?'selected':''?>><?=e($event['event_name'])?></option><?php endforeach?></select>
<small class="field-error"><?=e($errors['event_id']??'')?></small></div>
<div class="field">
<label for="event_date">Дата мероприятия</label>
<div class="date-entry">
<input id="event_date" name="event_date" data-date inputmode="numeric" placeholder="ДД.ММ.ГГГГ" value="<?=e($_POST['event_date']??'')?>" required>
<button type="button" id="open-calendar" aria-label="Выбрать дату в календаре">▦</button>
<input type="date" id="calendar" tabindex="-1" aria-label="Дата мероприятия — календарь"></div>
<small class="<?=isset($errors['event_date'])?'field-error':'hint'?>"><?=e($errors['event_date']??'Введите дату или выберите её в календаре')?></small></div>
<div class="field">
<label for="team">Состав команды</label>
<textarea id="team" name="team" rows="4" required placeholder="Иванов Иван Иванович&#10;Петрова Анна Сергеевна"><?=e($_POST['team']??'')?></textarea>
<small class="<?=isset($errors['team'])?'field-error':'hint'?>"><?=e($errors['team']??'ФИО каждого участника с новой строки')?></small></div>
<?php inputField('contact_phone','Телефон представителя команды','tel','8(999)123-45-67',$user['phone']);
    inputField('contact_email','Email представителя команды','email','',$user['email']);?>
<button class="button full" name="action" value="apply">Отправить</button><a class="form-link" href="my.php">Посмотреть мои регистрации</a>
</form></section>
<?php require __DIR__ . '/src/footer.php';
