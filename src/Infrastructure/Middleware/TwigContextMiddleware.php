<?php

/**
 * Twig Context Middleware
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
use Knowledgeroot\Domain\User\Service\UserService;
use Twig\Environment;

/**
 * Middleware to inject global context into Twig templates
 */
class TwigContextMiddleware implements MiddlewareInterface
{
    public function __construct(
        private SessionManager $sessionManager,
        private UserService $userService,
        private Environment $twig
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Add global variables to Twig
        $globals = [
            'is_logged_in' => $this->sessionManager->isLoggedIn(),
            'current_user' => null,
            'flash_messages' => $this->getFlashMessages(),
        ];

        // If user is logged in, fetch user data
        if ($this->sessionManager->isLoggedIn()) {
            $userId = $this->sessionManager->getUserId();
            if ($userId) {
                $user = $this->userService->getUserById($userId);
                if ($user) {
                    $globals['current_user'] = $user->toArray();
                }
            }
        }

        // Add all globals to Twig
        foreach ($globals as $key => $value) {
            $this->twig->addGlobal($key, $value);
        }

        return $handler->handle($request);
    }

    /**
     * Get all flash messages from session
     */
    private function getFlashMessages(): array
    {
        $messages = [];

        // Common flash message keys
        $keys = ['success', 'error', 'warning', 'info', 'message'];

        foreach ($keys as $key) {
            if ($this->sessionManager->hasFlash($key)) {
                $messages[$key] = $this->sessionManager->getFlash($key);
            }
        }

        return $messages;
    }
}
