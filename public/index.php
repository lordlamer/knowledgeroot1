<?php

/**
 * Knowledgeroot - Slim 4 Bootstrap
 *
 * Modern entry point using Slim Framework 4
 * This replaces the old monolithic index.php gradually
 *
 * @package Knowledgeroot
 */

declare(strict_types=1);

use Slim\Factory\AppFactory;
use DI\Container;

// Measure execution time
$startTime = microtime(true);

// Define base path
define('BASE_PATH', realpath(__DIR__ . '/..'));

// Check for configuration
if (!is_file(BASE_PATH . '/config/app.ini')) {
    die('<html><body>No configuration file found! Please run <a href="../install.php">install</a>!</body></html>');
}

// Load Composer autoloader
require BASE_PATH . '/vendor/autoload.php';

// Create DI Container
$container = new Container();

// Load container definitions
require BASE_PATH . '/config/dependencies.php';
$dependencies = $dependencies ?? function() {};
$dependencies($container);

// Create Slim App
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add error middleware
$errorMiddleware = $app->addErrorMiddleware(
    displayErrorDetails: true,
    logErrors: true,
    logErrorDetails: true
);

// Load middleware
require BASE_PATH . '/config/middleware.php';
$middleware = $middleware ?? function() {};
$middleware($app);

// Load routes
require BASE_PATH . '/config/routes.php';
$routes = $routes ?? function() {};
$routes($app);

// Run application
$app->run();

// Log runtime if debugging enabled
if (isset($container) && $container->has('config')) {
    $config = $container->get('config');
    if ($config->development->runtime ?? false) {
        $runtime = microtime(true) - $startTime;
        error_log(sprintf('Runtime: %.3f seconds', $runtime));
    }
}
