<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\UserManagement;

use Knowledgeroot\Domain\User\User;
use Knowledgeroot\Domain\User\UserRepository;

class UpdateUser
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    /**
     * @throws InvalidUserData
     */
    public function execute(int $id, UserFormData $data): void
    {
        $existing = $this->users->findById($id);
        if ($existing === null) {
            throw new InvalidUserData('User not found!');
        }

        if (trim($data->name) === '') {
            throw new InvalidUserData('Name must not be empty!');
        }

        if ($this->users->nameExists($data->name, $id)) {
            throw new InvalidUserData('A user with this name already exists!');
        }

        // empty password means: keep the current one
        $passwordHash = $data->password === ''
            ? $existing->passwordHash
            : password_hash($data->password, PASSWORD_DEFAULT);

        $user = new User(
            id: $id,
            name: $data->name,
            passwordHash: $passwordHash,
            defaultGroup: $data->defaultGroup,
            defaultRights: $data->defaultRights(),
            admin: $data->admin,
            rightEdit: $data->rightEdit,
            enabled: $data->enabled,
            treeCache: $existing->treeCache,
            theme: $data->theme,
            language: $existing->language,
        );

        $this->users->update($user, $data->groupIds);
    }
}
