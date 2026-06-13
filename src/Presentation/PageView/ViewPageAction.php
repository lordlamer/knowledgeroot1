<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\PageView;

use Knowledgeroot\Application\Navigation\BuildNavigation;
use Knowledgeroot\Application\PageView\ViewPage;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class ViewPageAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ViewPage $viewPage,
        private readonly BuildNavigation $buildNavigation,
        private readonly LegacySession $session,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $pageId = (int) $args['id'];
        $userId = $this->session->userId();

        $highlightParam = (string) ($request->getQueryParams()['highlight'] ?? '');
        $terms = $highlightParam === '' ? [] : explode(',', $highlightParam);

        $view = $this->viewPage->execute($pageId, $userId, $terms);

        if ($view === null) {
            return $response->withStatus(404);
        }

        $response->getBody()->write($this->twig->render('page/view.html', [
            'page' => $view->page,
            'breadcrumb' => $view->breadcrumb,
            'blocks' => $view->blocks,
            'can_edit_page' => $view->canEditPage,
            'navigation' => $this->buildNavigation->execute($userId, $pageId),
            'flashes' => $this->session->consumeFlashes(),
            'is_admin' => $this->session->isAdmin(),
        ]));

        return $response;
    }
}
