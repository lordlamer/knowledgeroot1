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

/**
 * Handles HTTP requests for user operations
 */
class UserController
{
    public function __construct(
        private UserService $userService
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
     * Authenticate user (login)
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
            $user = $this->userService->authenticate($username, $password);

            if (!$user) {
                $response->getBody()->write(json_encode([
                    'error' => 'Invalid credentials or inactive account'
                ]));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(401);
            }

            // Successful login
            $response->getBody()->write(json_encode([
                'message' => 'Login successful',
                'user' => $user->toArray()
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
