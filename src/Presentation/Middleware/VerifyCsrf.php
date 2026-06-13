<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Middleware;

use Knowledgeroot\Infrastructure\Security\Csrf;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as SlimResponse;

/**
 * Rejects state-changing requests without a valid CSRF token. The token
 * comes from the _csrf form field or the X-CSRF-Token header.
 */
class VerifyCsrf implements MiddlewareInterface
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(private readonly Csrf $csrf)
    {
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        if (in_array(strtoupper($request->getMethod()), self::SAFE_METHODS, true)) {
            return $handler->handle($request);
        }

        $body = (array) $request->getParsedBody();
        $token = $body[Csrf::FIELD] ?? $request->getHeaderLine('X-CSRF-Token');

        if (!$this->csrf->isValid(is_string($token) ? $token : null)) {
            return (new SlimResponse())->withStatus(419);
        }

        return $handler->handle($request);
    }
}
