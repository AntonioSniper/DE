<?php
$pageTitle = 'Регистрация';
require __DIR__ . '/src/bootstrap.php';
if (posted('register')) {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!preg_match('/^[A-Za-z0-9]{6,255}$/D', $login)) {
        $errors['login'] = 'Латиница и цифры, не менее 6 символов.';
    }
    if (mb_strlen($password) < 8) $errors['password'] = 'Не менее 8 символов.';
    if (!nameValid($name)) $errors['full_name'] = 'Кириллица и пробелы, до 255 символов.';
    if (!phoneValid($phone)) $errors['phone'] = 'Формат: 8(999)123-45-67.';
    if (!emailValid($email)) $errors['email'] = 'Введите корректный email.';

    if (!$errors) {
        try {
            $repo->query(
                'INSERT INTO users(role_id, login, password_hash, full_name, phone, email) VALUES(2, ?, ?, ?, ?, ?)',
                [$login, hashPassword($password), $name, $phone, $email]
            );
            flash('Регистрация завершена. Войдите в аккаунт.');
            go('login.php');
        } catch (PDOException $error) {
            if (($error->errorInfo[1] ?? null) !== 1062) throw $error;
            $errors['login'] = 'Этот логин уже занят.';
        }
    }
}
require __DIR__ . '/src/header.php';
?>
<div class="form-heading">
<p class="eyebrow">ПОРТАЛ «МЕРОПРИЯТИЯ»</p>
<h1><?=e($pageTitle)?></h1></div>
<section class="form-card">
<form method="post"><?=csrf()?>
<?php inputField('login','Логин','text','Латиница и цифры, не менее 6 символов');
    inputField('password','Пароль','password','Не менее 8 символов');
    inputField('full_name','ФИО','text','Кириллица и пробелы');
    inputField('phone','Телефон','tel','8(999)123-45-67');
    inputField('email','Электронная почта','email');?>
<button class="button full" name="action" value="register">Зарегистрироваться</button><a class="form-link" href="login.php">Уже есть аккаунт? Войти</a>
</form></section>
<?php require __DIR__ . '/src/footer.php';
