<?php

/**
 * User Controller
 *
 * @package Knowledgeroot\Application\Controller
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Knowledgeroot\Domain\User\Service\UserService;
use Knowledgeroot\Application\UseCase\Auth\LoginUseCase;
use Knowledgeroot\Infrastructure\Session\SessionManager;
use Twig\Environment;

/**
 * Handles HTTP requests for user operations
 */
class UserController
{
    public function __construct(
        private UserService $userService,
        private LoginUseCase $loginUseCase,
        private SessionManager $sessionManager,
        private Environment $twig
    ) {}

    /**
     * List all active users
     */
    public function list(Request $request, Response $response): Response
    {
        $users = $this->userService->getAllActiveUsers();

        $data = array_map(fn($user) => $user->toArray(), $users);

        $response->getBody()->write(json_encode([
            'count' => count($data),
            'users' => $data
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Get single user by ID
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $user = $this->userService->getUserById($id);

        if (!$user) {
            $response->getBody()->write(json_encode([
                'error' => 'User not found',
                'id' => $id
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $response->getBody()->write(json_encode($user->toArray()));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Show login form (HTML)
     */
    public function loginForm(Request $request, Response $response): Response
    {
        // If already logged in, redirect to home
        if ($this->sessionManager->isLoggedIn()) {
            return $response
                ->withHeader('Location', '/')
                ->withStatus(302);
        }

        $html = $this->twig->render('auth/login.html.twig', [
            'error' => $this->sessionManager->getFlash('error'),
            'username' => ''
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }

    /**
     * Process login form submission (HTML)
     */
    public function loginSubmit(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        $username = $body['username'] ?? '';
        $password = $body['password'] ?? '';
        $remember = isset($body['remember']);

        if (empty($username) || empty($password)) {
            $html = $this->twig->render('auth/login.html.twig', [
                'error' => 'Username and password are required',
                'username' => $username
            ]);

            $response->getBody()->write($html);
            return $response
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(400);
        }

        try {
            $result = $this->loginUseCase->execute($username, $password);

            if (!$result->isSuccess()) {
                $html = $this->twig->render('auth/login.html.twig', [
                    'error' => $result->getError(),
                    'username' => $username
                ]);

                $response->getBody()->write($html);
                return $response
                    ->withHeader('Content-Type', 'text/html')
                    ->withStatus(401);
            }

            // Login successful - create session
            $user = $result->getUser();
            $this->sessionManager->login($user, $remember);

            // Set flash message
            $this->sessionManager->setFlash('success', 'Welcome back, ' . $user->getUsername() . '!');

            // Check if there's a redirect URL from before login
            $redirectUrl = $this->sessionManager->getFlash('redirect_after_login', '/');

            return $response
                ->withHeader('Location', $redirectUrl)
                ->withStatus(302);

        } catch (\Exception $e) {
            $html = $this->twig->render('auth/login.html.twig', [
                'error' => 'An error occurred during login',
                'username' => $username
            ]);

            $response->getBody()->write($html);
            return $response
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(500);
        }
    }

    /**
     * Logout (HTML)
     */
    public function logout(Request $request, Response $response): Response
    {
        $this->sessionManager->logout();
        $this->sessionManager->setFlash('success', 'You have been logged out successfully.');

        return $response
            ->withHeader('Location', '/login')
            ->withStatus(302);
    }

    /**
     * Authenticate user (JSON API)
     */
    public function login(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        $username = $body['username'] ?? '';
        $password = $body['password'] ?? '';

        if (empty($username) || empty($password)) {
            $response->getBody()->write(json_encode([
                'error' => 'Username and password required'
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        try {
            $result = $this->loginUseCase->execute($username, $password);

            if (!$result->isSuccess()) {
                $response->getBody()->write(json_encode([
                    'error' => $result->getError()
                ]));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(401);
            }

            // Successful login
            $response->getBody()->write(json_encode([
                'message' => 'Login successful',
                'user' => $result->getUser()->toArray()
            ]));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Authentication failed'
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}
