<?php
// The built-in server serves only the same public pages and assets as Apache.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$pages = ['/', '/index.php', '/register.php', '/login.php', '/apply.php', '/my.php', '/admin.php'];
if (in_array($path, $pages, true)) {
    require __DIR__ . ($path === '/' ? '/index.php' : $path);
    return true;
}
if (preg_match('~^/assets/[a-zA-Z0-9_.-]+\.(css|js|webp)$~D', $path) && is_file(__DIR__ . $path)) {
    return false;
}
http_response_code(404);
echo 'Страница не найдена';
