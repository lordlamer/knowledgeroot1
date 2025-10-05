<?php

/**
 * Category Controller
 *
 * @package Knowledgeroot\Application\Controller
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Knowledgeroot\Application\UseCase\Category\ShowCategoryUseCase;
use Knowledgeroot\Application\UseCase\Category\GetCategoryTreeUseCase;
use Twig\Environment;

/**
 * Handles HTTP requests for category/tree operations
 */
class CategoryController
{
    public function __construct(
        private ShowCategoryUseCase $showCategoryUseCase,
        private GetCategoryTreeUseCase $getCategoryTreeUseCase,
        private Environment $twig
    ) {}

    /**
     * Show single category with contents (HTML)
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];

        try {
            $result = $this->showCategoryUseCase->execute($id);

            $html = $this->twig->render('category/show.html.twig', $result->toArray());

            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html');

        } catch (\DomainException $e) {
            $html = $this->twig->render('error/404.html.twig', [
                'message' => $e->getMessage()
            ]);

            $response->getBody()->write($html);
            return $response
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(404);
        }
    }

    /**
     * Get category tree (JSON API)
     */
    public function tree(Request $request, Response $response): Response
    {
        $currentId = $request->getQueryParams()['current_id'] ?? null;
        $currentId = $currentId !== null ? (int) $currentId : null;

        $result = $this->getCategoryTreeUseCase->execute($currentId);

        $response->getBody()->write(json_encode($result->toArray(), JSON_PRETTY_PRINT));

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Get single category by ID (JSON API)
     */
    public function showApi(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];

        try {
            $result = $this->showCategoryUseCase->execute($id);

            $response->getBody()->write(json_encode($result->toArray()));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\DomainException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage(),
                'id' => $id
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
    }

    /**
     * Get children of a category (JSON API)
     */
    public function children(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];

        try {
            $result = $this->showCategoryUseCase->execute($id);
            $children = $result->getChildCategories();

            $data = array_map(fn($cat) => $cat->toArray(), $children);

            $response->getBody()->write(json_encode([
                'category_id' => $id,
                'count' => count($data),
                'children' => $data
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (\DomainException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage(),
                'id' => $id
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
    }

    /**
     * Get category path (breadcrumb) (JSON API)
     */
    public function path(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];

        try {
            $result = $this->showCategoryUseCase->execute($id);
            $path = $result->getCategoryPath();

            $data = array_map(fn($cat) => $cat->toArray(), $path);

            $response->getBody()->write(json_encode([
                'category_id' => $id,
                'path' => $data
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (\DomainException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage(),
                'id' => $id
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
    }
}
