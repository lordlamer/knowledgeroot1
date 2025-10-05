<?php

/**
 * Login Use Case Response
 *
 * @package Knowledgeroot\Application\UseCase\Auth
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\UseCase\Auth;

use Knowledgeroot\Domain\User\Entity\User;

/**
 * Response DTO for LoginUseCase
 */
class LoginResponse
{
    public function __construct(
        public readonly User $user,
        public readonly bool $success
    ) {}

    public function toArray(): array
    {
        return [
            'user' => $this->user->toArray(),
            'success' => $this->success,
        ];
    }
}
