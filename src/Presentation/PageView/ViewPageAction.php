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

        $navigation = $this->buildNavigation->execute($userId, $pageId);

        $response->getBody()->write($this->twig->render('page/view.html', [
            'page' => $view->page,
            'breadcrumb' => $view->breadcrumb,
            'blocks' => $view->blocks,
            'can_edit_page' => $view->canEditPage,
            'navigation' => $navigation,
            'page_options' => $this->flatten($navigation),
            'flashes' => $this->session->consumeFlashes(),
            'is_admin' => $this->session->isAdmin(),
        ]));

        return $response;
    }

    /**
     * flatten the navigation tree into [{id, label}] with indentation,
     * for the "move to page" target selectors
     *
     * @param \Knowledgeroot\Domain\Navigation\NavigationNode[] $nodes
     * @return array<int, array{id: int, label: string}>
     */
    private function flatten(array $nodes, int $depth = 0): array
    {
        $out = [];
        foreach ($nodes as $node) {
            $out[] = ['id' => $node->id, 'label' => str_repeat('— ', $depth) . $node->title];
            foreach ($this->flatten($node->children, $depth + 1) as $child) {
                $out[] = $child;
            }
        }

        return $out;
    }
}
