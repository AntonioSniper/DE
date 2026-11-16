<?php
require_once __DIR__.'/../helpers/security.php';

$pageTitle   = $pageTitle   ?? 'Корочки.Есть';
$breadcrumbs = $breadcrumbs ?? [];
$content     = $content     ?? '';
$logoPath    = '/DE/korochki/media/image02.jpg';

// Динамическое меню в зависимости от роли
$navButtons = [];
if (isset($_SESSION['user_id'])) {
    if ((int)($_SESSION['user_role']??2)!==1) {
        $navButtons[] = ['title' => 'Мои заявки', 'url' => '/DE/korochki/pages/zayavki.php'];
        $navButtons[] = ['title' => 'Новая заявка', 'url' => '/DE/korochki/pages/new.php'];
    }
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1) {
        $navButtons[] = ['title' => 'Панель администратора', 'url' => '/DE/korochki/pages/admin.php'];
    }
    $navButtons[] = ['title' => 'Выход', 'url' => '/DE/korochki/pages/logout.php'];
} else {
    $navButtons[] = ['title' => 'Вход', 'url' => '/DE/korochki/pages/auth.php'];
    $navButtons[] = ['title' => 'Регистрация', 'url' => '/DE/korochki/pages/register.php'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/DE/korochki/assets/bootstrap.min.css">
    <link rel="stylesheet" href="/DE/korochki/styles/style.css?v=<?=filemtime(__DIR__.'/../styles/style.css')?>">
</head>
<body>

<header class="site-header">
    <div class="header-top">
        <a href="/DE/korochki/pages/index.php" class="logo">
            <img src="<?= $logoPath ?>" alt="Логотип">
            <span class="site-title">Корочки.есть</span>
        </a>
        <div class="header-actions">
            <?php foreach ($navButtons as $btn): ?>
                <?php if($btn['title']==='Выход'): ?><form action="/DE/korochki/pages/logout.php" method="post" class="logout-form"><?=csrf()?><button class="nav-btn" type="submit">Выход</button></form><?php else: ?><a href="<?= htmlspecialchars($btn['url']) ?>" class="nav-btn">
                    <?= htmlspecialchars($btn['title']) ?>
                </a><?php endif ?>
            <?php endforeach; ?>
        </div>
    </div>
</header>

<?php if (!empty($breadcrumbs)): ?>
    <nav class="breadcrumbs">
        <ul>
            <?php foreach ($breadcrumbs as $i => $crumb): ?>
                <li>
                    <?php if ($i < count($breadcrumbs) - 1 && !empty($crumb['url'])): ?>
                        <a href="<?= htmlspecialchars($crumb['url']) ?>"><?= htmlspecialchars($crumb['title']) ?></a>
                    <?php else: ?>
                        <span class="current"><?= htmlspecialchars($crumb['title']) ?></span>
                    <?php endif; ?>
                    <?php if ($i < count($breadcrumbs) - 1): ?><span class="separator">/</span><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
<?php endif; ?>

<main class="site-content">
    <?php
    // Токен добавляется во все формы, созданные страницами.
    echo preg_replace_callback('/<form\b[^>]*method=["\']post["\'][^>]*>/i',fn($m)=>$m[0].csrf(),$content);
    ?>
</main>

<footer class="site-footer">
    <p>&copy; <?= date('Y') ?> Корочки.Есть — портал онлайн-курсов дополнительного профессионального образования.</p>
</footer>

<script src="/DE/korochki/scripts/script.js?v=<?=filemtime(__DIR__.'/../scripts/script.js')?>"></script>
</body>
</html>