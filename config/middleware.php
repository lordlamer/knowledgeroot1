<?php

/**
 * Application Middleware
 *
 * @package Knowledgeroot
 */

declare(strict_types=1);

use Slim\App;
use Knowledgeroot\Infrastructure\Middleware\TwigContextMiddleware;

return function (App $app) {
    // Add Routing Middleware (must be before routes)
    $app->addRoutingMiddleware();

    // Add Body Parsing Middleware
    $app->addBodyParsingMiddleware();

    // Add Twig Context Middleware (adds session/user data to templates)
    // This runs for every request to inject global variables into Twig
    $app->add(TwigContextMiddleware::class);

    // Note: AuthenticationMiddleware is added per-route basis in routes.php
    // This allows us to protect only specific routes that need authentication
};
