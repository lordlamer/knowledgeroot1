<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\PrintView;

use Knowledgeroot\Application\PrintView\PrintContent;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class PrintContentAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly PrintContent $printContent,
        private readonly LegacySession $session,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $content = $this->printContent->execute((int) $args['id'], $this->session->userId());

        if ($content === null) {
            return $response->withStatus(404);
        }

        $response->getBody()->write($this->twig->render('content/print.html', [
            'title' => $content->title,
            'html' => $content->html,
        ]));

        return $response;
    }
}
