<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\UserSettings;

use Knowledgeroot\Application\UserManagement\InvalidUserData;
use Knowledgeroot\Domain\User\UserRepository;

/**
 * Use case: a logged in user changes their own password.
 */
class ChangeOwnPassword
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    /**
     * @throws InvalidUserData
     */
    public function execute(int $userId, string $password, string $confirmation): void
    {
        if ($userId <= 0 || $this->users->findById($userId) === null) {
            throw new InvalidUserData('User not found!');
        }

        if ($password === '') {
            throw new InvalidUserData('Password must not be empty!');
        }

        if ($password !== $confirmation) {
            throw new InvalidUserData('Failed to change password!');
        }

        $this->users->updatePassword($userId, password_hash($password, PASSWORD_DEFAULT));
    }
}
