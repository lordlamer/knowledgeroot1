<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\UserManagement;

use Knowledgeroot\Domain\Group\GroupRepository;

class UpdateGroup
{
    public function __construct(private readonly GroupRepository $groups)
    {
    }

    /**
     * @throws InvalidUserData
     */
    public function execute(int $id, string $name): void
    {
        if ($this->groups->findById($id) === null) {
            throw new InvalidUserData('Group not found!');
        }

        if (trim($name) === '') {
            throw new InvalidUserData('Name must not be empty!');
        }

        $this->groups->rename($id, trim($name));
    }
}
