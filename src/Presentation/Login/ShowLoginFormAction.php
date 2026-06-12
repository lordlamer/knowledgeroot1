<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Login;

use Knowledgeroot\Infrastructure\Session\LegacySession;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class ShowLoginFormAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly LegacySession $session,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        if ($this->session->isLoggedIn()) {
            return $response->withHeader('Location', 'index.php')->withStatus(302);
        }

        $response->getBody()->write($this->twig->render('login/index.html', [
            'user' => '',
            'error' => null,
        ]));

        return $response;
    }
}
