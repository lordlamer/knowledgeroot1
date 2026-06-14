<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Login;

use Knowledgeroot\Infrastructure\Session\LegacySession;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LogoutAction
{
    public function __construct(private readonly LegacySession $session)
    {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $this->session->logout();

        return $response->withHeader('Location', 'index.php')->withStatus(302);
    }
}
