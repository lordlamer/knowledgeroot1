<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Middleware;

use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as SlimResponse;

/**
 * Guards routes that need a logged in user: guests are sent to the
 * login form.
 */
class RequireLogin implements MiddlewareInterface
{
    public function __construct(
        private readonly LegacySession $session,
        private readonly UrlHelper $url,
    ) {
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        if (!$this->session->isLoggedIn()) {
            return (new SlimResponse())->withHeader('Location', $this->url->to('login'))->withStatus(302);
        }

        return $handler->handle($request);
    }
}
