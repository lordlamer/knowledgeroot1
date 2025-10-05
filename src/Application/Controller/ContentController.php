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
use Knowledgeroot\Application\UseCase\Content\ShowContentUseCase;
use Knowledgeroot\Application\UseCase\Content\ListContentByCategoryUseCase;
use Twig\Environment;

/**
 * Handles HTTP requests for content operations
 */
class ContentController
{
    public function __construct(
        private ShowContentUseCase $showContentUseCase,
        private ListContentByCategoryUseCase $listContentByCategoryUseCase,
        private Environment $twig
    ) {}

    /**
     * Show single content item (HTML)
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];

        try {
            $result = $this->showContentUseCase->execute($id);

            $html = $this->twig->render('content/show.html.twig', $result->toArray());

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
     * Show single content item (JSON API)
     */
    public function showApi(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];

        try {
            $result = $this->showContentUseCase->execute($id);

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
     * List all content in a category (HTML)
     */
    public function listByCategory(Request $request, Response $response, array $args): Response
    {
        $categoryId = (int) $args['categoryId'];

        try {
            $result = $this->listContentByCategoryUseCase->execute($categoryId);

            $html = $this->twig->render('content/list.html.twig', $result->toArray());

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
     * List all content in a category (JSON API)
     */
    public function listByCategoryApi(Request $request, Response $response, array $args): Response
    {
        $categoryId = (int) $args['categoryId'];

        try {
            $result = $this->listContentByCategoryUseCase->execute($categoryId);

            $response->getBody()->write(json_encode($result->toArray()));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\DomainException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage(),
                'category_id' => $categoryId
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
    }
}
