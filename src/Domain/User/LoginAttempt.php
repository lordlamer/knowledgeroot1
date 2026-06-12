<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\User;

class LoginAttempt
{
    public function __construct(
        public readonly int $userId,
        public readonly int $trials,
        public readonly int $lastTryDate,
    ) {
    }
}
