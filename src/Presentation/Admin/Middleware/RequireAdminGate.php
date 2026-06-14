<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Admin\Middleware;

use Knowledgeroot\Infrastructure\Admin\AdminGate;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as SlimResponse;

/**
 * Guards the admin backend: requests without a valid admin session are
 * sent to the admin login.
 */
class RequireAdminGate implements MiddlewareInterface
{
    public function __construct(
        private readonly AdminGate $gate,
        private readonly UrlHelper $url,
    ) {
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        if (!$this->gate->isAuthenticated()) {
            return (new SlimResponse())->withHeader('Location', $this->url->to('admin/login'))->withStatus(302);
        }

        return $handler->handle($request);
    }
}
