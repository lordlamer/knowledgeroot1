<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\Login;

use Knowledgeroot\Domain\User\User;

class AuthenticationResult
{
    private function __construct(
        public readonly ?User $user,
        public readonly ?AuthenticationFailure $failure,
    ) {
    }

    public static function success(User $user): self
    {
        return new self($user, null);
    }

    public static function failure(AuthenticationFailure $reason): self
    {
        return new self(null, $reason);
    }

    public function isSuccess(): bool
    {
        return $this->user !== null;
    }
}
