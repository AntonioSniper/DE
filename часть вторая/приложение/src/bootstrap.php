<?php
declare(strict_types=1);
date_default_timezone_set('Europe/Moscow');
ini_set('session.use_strict_mode','1');
session_name('college_events_session');
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','path'=>'/']);
session_start();
header('X-Content-Type-Options: nosniff');header('X-Frame-Options: SAMEORIGIN');
$_SESSION['csrf']??=bin2hex(random_bytes(32));
function e($s):string{return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function csrf():string{return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">';}

function go(string $file, array $params = []): never {
    $query = http_build_query($params);
    header('Location: ' . $file . ($query === '' ? '' : '?' . $query));
    exit;
}
function flash(string $message):void{$_SESSION['flash']=$message;}
function phoneValid(string $phone):bool{return (bool)preg_match('/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/D',$phone);}
function nameValid(string $name):bool{return mb_strlen($name)<=255 && (bool)preg_match('/^[А-Яа-яЁё]+(?: +[А-Яа-яЁё]+)*$/uD',$name);}
function emailValid(string $email): bool {
    return strlen($email) <= 254 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
function hashPassword(string $password):string{
 if(defined('PASSWORD_ARGON2ID'))return password_hash($password,PASSWORD_ARGON2ID);
 return 'sha256:'.password_hash(hash('sha256',$password),PASSWORD_DEFAULT);
}
function passwordMatches(string $password,string $hash):bool{
 return str_starts_with($hash,'sha256:')?password_verify(hash('sha256',$password),substr($hash,7)):password_verify($password,$hash);
}

require_once __DIR__ . '/PortalRepository.php';

function errorPage(int $code, string $pageTitle): never {
    global $user, $errors;
    http_response_code($code);
    require __DIR__ . '/header.php';
    echo '<section class="empty"><h1>' . e($pageTitle) . '</h1><a href="index.php">Вернуться на главную</a></section>';
    require __DIR__ . '/footer.php';
    exit;
}

function requireUser(): void {
    global $user;
    if (!$user) go('login.php');
}

function posted(string $action): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === $action;
}

$errors = [];
$user = null;
foreach ([$_GET, $_POST] as $fields) {
    foreach ($fields as $value) {
        if (!is_string($value) || str_contains($value, "\0") || !mb_check_encoding($value, 'UTF-8')) {
            errorPage(400, 'Некорректный формат данных формы.');
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
    errorPage(403, 'Форма устарела. Обновите страницу и повторите отправку.');
}
if (posted('logout')) {
    $_SESSION = [];
    session_regenerate_id(true);
    go('index.php');
}

set_exception_handler(static function (Throwable $error): void {
    error_log($error->getMessage());
    errorPage(503, 'Сервис временно недоступен. Проверьте подключение к базе данных.');
});
$repo = new PortalRepository(Database::connect());
if (isset($_SESSION['user_id'])) {
    $user = $repo->user((int)$_SESSION['user_id']) ?: null;
    if (!$user) unset($_SESSION['user_id']);
}
function inputField(string $name,string $label,string $type='text',string $hint='',string $default=''):void{
 global $errors,$pageTitle;
 echo '<div class="field"><label for="'.e($name).'">'.e($label).'</label><input id="'.e($name).'" name="'.e($name).'" type="'.e($type).'" required '.($type==='password'?'autocomplete="'.($pageTitle==='Авторизация'?'current-password':'new-password').'"':'value="'.e($_POST[$name]??$default).'"').' '.($type==='tel'?'inputmode="numeric" autocomplete="tel" data-phone':'').' '.(isset($errors[$name])?'aria-invalid="true"':'').'>';
 echo '<small class="'.(isset($errors[$name])?'field-error':'hint').'">'.e($errors[$name]??$hint).'</small></div>';
}
