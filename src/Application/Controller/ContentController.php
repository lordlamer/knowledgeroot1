<?php

/**
 * Content Controller
 *
 * @package Knowledgeroot\Application\Controller
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Knowledgeroot\Domain\Content\Service\ContentService;
use Twig\Environment;

/**
 * Handles HTTP requests for content operations
 */
class ContentController
{
    public function __construct(
        private ContentService $contentService,
        private Environment $twig
    ) {}

    /**
     * Show single content item
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $content = $this->contentService->getContentById($id);

        if (!$content) {
            $response->getBody()->write(json_encode([
                'error' => 'Content not found',
                'id' => $id
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        // Return JSON for API
        $response->getBody()->write(json_encode($content->toArray()));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * List all content in a category
     */
    public function listByCategory(Request $request, Response $response, array $args): Response
    {
        $categoryId = (int) $args['categoryId'];
        $contents = $this->contentService->getContentsByCategory($categoryId);

        $data = array_map(fn($content) => $content->toArray(), $contents);

        $response->getBody()->write(json_encode([
            'category_id' => $categoryId,
            'count' => count($data),
            'items' => $data
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
