<?php
declare(strict_types=1);
date_default_timezone_set('Europe/Moscow');
if(session_status()===PHP_SESSION_NONE) session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
function e($s):string{return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function csrf():string{return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">';}
if($_SERVER['REQUEST_METHOD']==='POST' && !hash_equals($_SESSION['csrf'],(string)($_POST['csrf']??''))){http_response_code(403);exit('Сессия формы устарела. Вернитесь назад и обновите страницу.');}
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
