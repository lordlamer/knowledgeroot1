<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\UserManagement;

use Knowledgeroot\Domain\Group\GroupRepository;
use Knowledgeroot\Domain\User\UserRepository;

/**
 * Use case: list all users and groups for the user management page.
 */
class GetUserOverview
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly GroupRepository $groups,
    ) {
    }

    public function execute(): UserOverview
    {
        $groups = $this->groups->findAll();

        $groupNames = [];
        foreach ($groups as $group) {
            $groupNames[$group->id] = $group->name;
        }

        return new UserOverview($this->users->findAll(), $groups, $groupNames);
    }
}
