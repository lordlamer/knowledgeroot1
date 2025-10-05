<?php

/**
 * Home Controller
 *
 * @package Knowledgeroot\Application\Controller
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Knowledgeroot\Application\UseCase\Home\ShowDashboardUseCase;
use Twig\Environment;

/**
 * Handles homepage requests
 */
class HomeController
{
    public function __construct(
        private ShowDashboardUseCase $showDashboardUseCase,
        private Environment $twig
    ) {}

    /**
     * Homepage/Dashboard (HTML)
     */
    public function index(Request $request, Response $response): Response
    {
        $result = $this->showDashboardUseCase->execute();

        $html = $this->twig->render('home/dashboard.html.twig', $result->toArray());

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }

    /**
     * API info endpoint (JSON)
     */
    public function apiInfo(Request $request, Response $response): Response
    {
        $data = [
            'message' => 'Welcome to Knowledgeroot API',
            'version' => '2.0-dev',
            'framework' => 'Slim 4',
            'php_version' => PHP_VERSION,
            'endpoints' => [
                'Content API' => [
                    'GET /api/content/{id}' => 'Get content by ID',
                    'GET /api/content/category/{categoryId}' => 'List content by category',
                ],
                'User API' => [
                    'GET /api/users' => 'List all active users',
                    'GET /api/users/{id}' => 'Get user by ID',
                    'POST /api/auth/login' => 'Authenticate user (username, password)',
                ],
                'Category API' => [
                    'GET /api/categories/tree' => 'Get category tree (hierarchical)',
                    'GET /api/categories/{id}' => 'Get category by ID',
                    'GET /api/categories/{id}/children' => 'Get children of category',
                    'GET /api/categories/{id}/path' => 'Get category path (breadcrumb)',
                ],
            ]
        ];

        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
