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
 * /admin so the routes (defined without a trailing slash) match. The
 * decision is made on the path relative to the base path, so the app
 * root itself (e.g. /public/ when served from the project root) is
 * never redirected into a non-matching path.
 */
class TrailingSlash implements MiddlewareInterface
{
    public function __construct(private readonly string $basePath = '')
    {
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        $relative = $path;
        if ($this->basePath !== '' && str_starts_with($path, $this->basePath)) {
            $relative = substr($path, strlen($this->basePath));
        }

        if ($relative !== '' && $relative !== '/' && str_ends_with($path, '/')) {
            $location = rtrim($path, '/');
            if ($uri->getQuery() !== '') {
                $location .= '?' . $uri->getQuery();
            }

            return (new SlimResponse())->withHeader('Location', $location)->withStatus(301);
        }

        return $handler->handle($request);
    }
}
