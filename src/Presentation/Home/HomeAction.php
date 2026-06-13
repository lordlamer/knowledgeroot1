<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Home;

use Knowledgeroot\Application\Navigation\BuildNavigation;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

/**
 * The front page: navigation tree plus a welcome panel. Replaces the
 * legacy index.php home flow.
 */
class HomeAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly BuildNavigation $buildNavigation,
        private readonly LegacySession $session,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        // redirect bookmarkable legacy deep links to their new routes
        $query = $request->getQueryParams();
        $target = match (true) {
            (int) ($query['id'] ?? 0) > 0 => 'page/' . (int) $query['id'],
            (int) ($query['download'] ?? 0) > 0 => 'file/' . (int) $query['download'],
            (int) ($query['eid'] ?? 0) > 0 => 'content/' . (int) $query['eid'] . '/edit',
            ($query['action'] ?? '') === 'createroot' => 'page/root/new',
            default => null,
        };
        if ($target !== null) {
            return $response->withHeader('Location', $target)->withStatus(302);
        }

        $response->getBody()->write($this->twig->render('home/index.html', [
            'navigation' => $this->buildNavigation->execute($this->session->userId(), 0),
            'flashes' => $this->session->consumeFlashes(),
        ]));

        return $response;
    }
}
