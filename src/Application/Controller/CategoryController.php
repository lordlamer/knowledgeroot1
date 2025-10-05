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
use Knowledgeroot\Domain\Category\Service\CategoryService;

/**
 * Handles HTTP requests for category/tree operations
 */
class CategoryController
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    /**
     * Get category tree (hierarchical)
     */
    public function tree(Request $request, Response $response): Response
    {
        $parentId = $request->getQueryParams()['parent_id'] ?? null;
        $parentId = $parentId !== null ? (int) $parentId : null;

        $tree = $this->categoryService->getCategoryTree($parentId);

        $response->getBody()->write(json_encode([
            'parent_id' => $parentId,
            'tree' => $tree
        ], JSON_PRETTY_PRINT));

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Get single category by ID
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $category = $this->categoryService->getCategoryById($id);

        if (!$category) {
            $response->getBody()->write(json_encode([
                'error' => 'Category not found',
                'id' => $id
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $response->getBody()->write(json_encode($category->toArray()));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Get children of a category
     */
    public function children(Request $request, Response $response, array $args): Response
    {
        $parentId = isset($args['parentId']) ? (int) $args['parentId'] : null;
        $children = $this->categoryService->getChildCategories($parentId);

        $data = array_map(fn($cat) => $cat->toArray(), $children);

        $response->getBody()->write(json_encode([
            'parent_id' => $parentId,
            'count' => count($data),
            'children' => $data
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Get category path (breadcrumb)
     */
    public function path(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $path = $this->categoryService->getCategoryPath($id);

        $data = array_map(fn($cat) => $cat->toArray(), $path);

        $response->getBody()->write(json_encode([
            'category_id' => $id,
            'path' => $data
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
