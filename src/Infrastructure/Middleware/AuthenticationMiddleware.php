<?php

/**
 * Authentication Middleware
 *
 * @package Knowledgeroot\Infrastructure\Middleware
 */

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Knowledgeroot\Infrastructure\Session\SessionManager;
use Slim\Psr7\Response;

/**
 * Middleware to protect routes that require authentication
 */
class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private SessionManager $sessionManager
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Check if user is logged in
        if (!$this->sessionManager->isLoggedIn()) {
            // Not logged in - redirect to login page
            $response = new Response();

            // Store the intended URL to redirect back after login
            $requestedUri = (string) $request->getUri();
            $this->sessionManager->setFlash('redirect_after_login', $requestedUri);

            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        // User is logged in - add user info to request attributes
        $request = $request
            ->withAttribute('user_id', $this->sessionManager->getUserId())
            ->withAttribute('username', $this->sessionManager->getUsername());

        return $handler->handle($request);
    }
}
