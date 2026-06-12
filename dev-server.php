<?php

/**
 * Router script for the PHP built-in webserver (development only).
 * Serves existing files directly, everything else goes through the
 * front controller - same behaviour as the apache rewrite rules.
 *
 * Usage: php -S 127.0.0.1:8080 dev-server.php
 */

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// deny the same directories apache blocks via .htaccess
if (preg_match('#^/(data|config|include|src|dumps|vendor|system/(templates|language))/#', $path)) {
    http_response_code(403);
    exit;
}

if ($path !== '/' && $path !== '/index.php' && is_file(__DIR__ . $path)) {
    return false;
}

require __DIR__ . '/index.php';
