<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\UserManagement;

use Knowledgeroot\Domain\Group\GroupRepository;

class CreateGroup
{
    public function __construct(private readonly GroupRepository $groups)
    {
    }

    /**
     * @return int id of the new group
     * @throws InvalidUserData
     */
    public function execute(string $name): int
    {
        if (trim($name) === '') {
            throw new InvalidUserData('Name must not be empty!');
        }

        return $this->groups->add(trim($name));
    }
}
