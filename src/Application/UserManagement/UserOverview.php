<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\UserManagement;

use Knowledgeroot\Domain\Group\Group;
use Knowledgeroot\Domain\User\User;

class UserOverview
{
    /**
     * @param User[] $users
     * @param Group[] $groups
     * @param array<int, string> $groupNames group id => name
     */
    public function __construct(
        public readonly array $users,
        public readonly array $groups,
        public readonly array $groupNames,
    ) {
    }
}
