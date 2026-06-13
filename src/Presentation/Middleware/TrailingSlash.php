<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as SlimResponse;

/**
 * Normalises trailing slashes: a request to /admin/ is redirected to
 * /admin so the routes (which are defined without a trailing slash)
 * match. The root path "/" is left untouched.
 */
class TrailingSlash implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        if ($path !== '/' && str_ends_with($path, '/')) {
            $location = rtrim($path, '/');
            if ($uri->getQuery() !== '') {
                $location .= '?' . $uri->getQuery();
            }

            return (new SlimResponse())->withHeader('Location', $location)->withStatus(301);
        }

        return $handler->handle($request);
    }
}
