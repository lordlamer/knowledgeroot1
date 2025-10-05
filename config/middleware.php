<?php

/**
 * Application Middleware
 *
 * @package Knowledgeroot
 */

declare(strict_types=1);

use Slim\App;

return function (App $app) {
    // Add Routing Middleware
    $app->addRoutingMiddleware();

    // Add Body Parsing Middleware
    $app->addBodyParsingMiddleware();

    // Session Middleware would go here
    // TODO: Implement SessionMiddleware from legacy session class

    // Authentication Middleware would go here
    // TODO: Implement AuthMiddleware from legacy auth class
};
