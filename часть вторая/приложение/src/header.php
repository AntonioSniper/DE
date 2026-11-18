<!doctype html><html lang="ru"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($pageTitle)?> — Мероприятия</title>
<link rel="stylesheet" href="assets/bootstrap.min.css">
<link rel="stylesheet" href="assets/style.css?v=<?=filemtime(dirname(__DIR__).'/assets/style.css')?>"></head><body>
<header>
<div class="shell header-inner"><a class="brand" href="index.php"><span class="brand-mark" aria-hidden="true">м</span><span>Мероприятия<small>ВНЕУЧЕБНАЯ ЖИЗНЬ ТЕХНИКУМА</small></span></a>
<nav aria-label="Основная навигация">
<?php if($user):if((int)$user['role_id']===1):?><a href="admin.php">Панель администратора</a><?php else:?><a href="my.php">Мои регистрации</a><a class="nav-button" href="apply.php">Подать заявку</a><?php endif?>
<form method="post"><?=csrf()?>
<button class="logout" name="action" value="logout">Выйти</button></form><?php else:?><a href="login.php">Вход</a><a class="nav-button" href="register.php">Регистрация</a><?php endif?></nav></div></header>
<main class="shell">
<?php if($pageTitle !== 'Внеучебная жизнь техникума'):?>
<div class="breadcrumbs"><a href="index.php">Главная</a><span>/</span><?=e($pageTitle)?></div><?php endif?>
<?php if(isset($_SESSION['flash'])):?>
<div class="notice flash-popup" role="status"><?=e($_SESSION['flash'])?>
<button type="button" class="dismiss" aria-label="Закрыть уведомление">×</button></div><?php unset($_SESSION['flash']);endif?>
<?php if(isset($errors['general'])):?>
<div class="notice error" role="alert"><?=e($errors['general'])?></div><?php endif?>
