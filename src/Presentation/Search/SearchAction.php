<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Search;

use Knowledgeroot\Application\Search\Search;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class SearchAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly Search $search,
        private readonly LegacySession $session,
        private readonly UrlHelper $url,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $query = trim((string) ($request->getQueryParams()['q'] ?? ''));

        $outcome = $this->search->execute($query, $this->session->userId());

        if ($outcome->isJump()) {
            return $response
                ->withHeader('Location', $this->url->to('index.php?id=' . $outcome->jumpToPageId . '#' . $outcome->jumpToContentId))
                ->withStatus(302);
        }

        // the legacy page view highlights the searched words via this parameter
        $highlight = urlencode(str_replace(' ', ',', $query));

        $response->getBody()->write($this->twig->render('search/index.html', [
            'query' => $query,
            'highlight' => $highlight,
            'content' => $outcome->content,
            'pages' => $outcome->pages,
            'files' => $outcome->files,
        ]));

        return $response;
    }
}
