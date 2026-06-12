<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\UserManagement;

use Knowledgeroot\Domain\User\User;
use Knowledgeroot\Domain\User\UserRepository;

class CreateUser
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    /**
     * @return int id of the new user
     * @throws InvalidUserData
     */
    public function execute(UserFormData $data): int
    {
        if (trim($data->name) === '') {
            throw new InvalidUserData('Name must not be empty!');
        }

        if ($data->password === '') {
            throw new InvalidUserData('Password must not be empty!');
        }

        if ($this->users->nameExists($data->name)) {
            throw new InvalidUserData('A user with this name already exists!');
        }

        $user = new User(
            id: 0,
            name: $data->name,
            passwordHash: password_hash($data->password, PASSWORD_DEFAULT),
            defaultGroup: $data->defaultGroup,
            defaultRights: $data->defaultRights(),
            admin: $data->admin,
            rightEdit: $data->rightEdit,
            enabled: $data->enabled,
            treeCache: '',
            theme: $data->theme,
            language: '',
        );

        return $this->users->add($user, $data->groupIds);
    }
}
