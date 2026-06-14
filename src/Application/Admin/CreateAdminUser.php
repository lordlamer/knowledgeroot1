<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\Admin;

use Knowledgeroot\Application\UserManagement\InvalidUserData;
use Knowledgeroot\Domain\User\User;
use Knowledgeroot\Domain\User\UserRepository;

/**
 * Break-glass: create an enabled administrator from the admin backend
 * (default group 1, full default rights, rightedit) - the recovery path
 * for when you are locked out. Mirrors the legacy admin_recover, but
 * stores a modern password hash.
 */
class CreateAdminUser
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    /**
     * @return int id of the new admin user
     * @throws InvalidUserData
     */
    public function execute(string $name, string $password): int
    {
        $name = trim($name);

        if ($name === '' || $password === '') {
            throw new InvalidUserData('Could not create user');
        }

        if ($this->users->nameExists($name)) {
            throw new InvalidUserData('A user with this name already exists!');
        }

        $user = new User(
            id: 0,
            name: $name,
            passwordHash: password_hash($password, PASSWORD_DEFAULT),
            defaultGroup: 1,
            defaultRights: 220,
            admin: true,
            rightEdit: true,
            enabled: true,
            treeCache: '',
            theme: 'green',
            language: '',
        );

        return $this->users->add($user, []);
    }
}
