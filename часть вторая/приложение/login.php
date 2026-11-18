<?php
$pageTitle = 'Авторизация';
require __DIR__ . '/src/bootstrap.php';
if (posted('login')) {
    $account = $repo->query(
        'SELECT user_id, role_id, password_hash FROM users WHERE login=?',
        [trim($_POST['login'] ?? '')]
    )->fetch();
    if ($account && passwordMatches($_POST['password'] ?? '', $account['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $account['user_id'];
        go((int)$account['role_id'] === 1 ? 'admin.php' : 'my.php');
    }
    $errors['general'] = 'Неверный логин или пароль.';
}
require __DIR__ . '/src/header.php';
?>
<div class="form-heading">
<p class="eyebrow">ПОРТАЛ «МЕРОПРИЯТИЯ»</p>
<h1><?=e($pageTitle)?></h1></div>
<section class="form-card">
<form method="post"><?=csrf()?>
<?php inputField('login','Логин');
    inputField('password','Пароль','password');?>
<button class="button full" name="action" value="login">Войти в аккаунт</button><a class="form-link" href="register.php">Еще не зарегистрированы? Регистрация</a>
</form></section>
<?php require __DIR__ . '/src/footer.php';
