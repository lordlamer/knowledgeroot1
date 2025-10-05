<?php

/**
 * Login Use Case
 *
 * @package Knowledgeroot\Application\UseCase\Auth
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\UseCase\Auth;

use Knowledgeroot\Domain\User\Service\UserService;
use Knowledgeroot\Domain\User\Entity\User;

/**
 * Use Case: Authenticate user and create session
 */
class LoginUseCase
{
    public function __construct(
        private UserService $userService
    ) {}

    public function execute(string $username, string $password): LoginResponse
    {
        // Validate input
        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException("Username and password are required");
        }

        // Authenticate
        $user = $this->userService->authenticate($username, $password);

        if (!$user) {
            throw new \DomainException("Invalid credentials or inactive account");
        }

        return new LoginResponse(
            user: $user,
            success: true
        );
    }
}
