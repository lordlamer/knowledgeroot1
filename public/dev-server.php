<?php

/**
 * Router script for the PHP built-in webserver (development only).
 * Serves existing static files in public/ directly, routes everything
 * else through the front controller - same behaviour as the apache
 * rewrite in public/.htaccess.
 *
 * Usage: php -S 127.0.0.1:8080 -t public public/dev-server.php
 */

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path !== '/' && $path !== '/index.php' && is_file(__DIR__ . $path)) {
    return false;
}

require __DIR__ . '/index.php';
