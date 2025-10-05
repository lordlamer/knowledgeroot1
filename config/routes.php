<?php

/**
 * Application Routes
 *
 * @package Knowledgeroot
 */

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Knowledgeroot\Application\Controller\HomeController;
use Knowledgeroot\Application\Controller\ContentController;
use Knowledgeroot\Application\Controller\UserController;
use Knowledgeroot\Application\Controller\CategoryController;
use Knowledgeroot\Infrastructure\Middleware\AuthenticationMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    // Public Routes (No Auth Required)
    $app->get('/login', [UserController::class, 'loginForm'])->setName('auth.login.form');
    $app->post('/login', [UserController::class, 'loginSubmit'])->setName('auth.login.submit');

    // Protected HTML Routes (Require Authentication)
    $app->get('/', [HomeController::class, 'index'])
        ->setName('home')
        ->add(AuthenticationMiddleware::class);

    $app->get('/content/{id:\d+}', [ContentController::class, 'show'])
        ->setName('content.show')
        ->add(AuthenticationMiddleware::class);

    $app->get('/category/{categoryId:\d+}', [ContentController::class, 'listByCategory'])
        ->setName('content.list')
        ->add(AuthenticationMiddleware::class);

    $app->get('/category/{id:\d+}', [CategoryController::class, 'show'])
        ->setName('category.show')
        ->add(AuthenticationMiddleware::class);

    $app->get('/logout', [UserController::class, 'logout'])
        ->setName('auth.logout')
        ->add(AuthenticationMiddleware::class);

    // API Routes (JSON)
    $app->group('/api', function (RouteCollectorProxy $group) {
        // API Info
        $group->get('', [HomeController::class, 'apiInfo']);

        // Content API
        $group->get('/content/{id:\d+}', [ContentController::class, 'showApi']);
        $group->get('/content/category/{categoryId:\d+}', [ContentController::class, 'listByCategoryApi']);

        // User API
        $group->get('/users', [UserController::class, 'list']);
        $group->get('/users/{id:\d+}', [UserController::class, 'show']);
        $group->post('/auth/login', [UserController::class, 'login']);

        // Category/Tree API
        $group->get('/categories/tree', [CategoryController::class, 'tree']);
        $group->get('/categories/{id:\d+}', [CategoryController::class, 'showApi']);
        $group->get('/categories/{id:\d+}/children', [CategoryController::class, 'children']);
        $group->get('/categories/{id:\d+}/path', [CategoryController::class, 'path']);
    });

    // Legacy fallback route
    // This catches all other requests and forwards them to the old index.php
    // Remove this once full migration is complete
    $app->get('/{path:.*}', function (Request $request, Response $response) {
        // Include legacy application
        $_GET = $request->getQueryParams();
        $_SERVER['REQUEST_URI'] = $request->getUri()->getPath();

        ob_start();
        include BASE_PATH . '/index.php';
        $output = ob_get_clean();

        $response->getBody()->write($output);
        return $response;
    })->setName('legacy');
};
