<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\Admin;

use Knowledgeroot\Application\UserManagement\InvalidUserData;
use Knowledgeroot\Domain\User\UserRepository;

/**
 * Break-glass: reset any user's password from the admin backend.
 */
class ResetUserPassword
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    /**
     * @throws InvalidUserData
     */
    public function execute(int $userId, string $password): void
    {
        if ($this->users->findById($userId) === null) {
            throw new InvalidUserData('User not found!');
        }

        if ($password === '') {
            throw new InvalidUserData('Password must not be empty!');
        }

        $this->users->updatePassword($userId, password_hash($password, PASSWORD_DEFAULT));
    }
}
