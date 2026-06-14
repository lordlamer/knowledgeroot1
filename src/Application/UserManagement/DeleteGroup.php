<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\UserManagement;

use Knowledgeroot\Domain\Group\GroupRepository;
use Knowledgeroot\Domain\User\UserRepository;

class DeleteGroup
{
    public function __construct(
        private readonly GroupRepository $groups,
        private readonly UserRepository $users,
    ) {
    }

    /**
     * @return bool false when the group is still used as a default group
     */
    public function execute(int $id): bool
    {
        if ($this->groups->findById($id) === null) {
            return false;
        }

        if ($this->users->countByDefaultGroup($id) > 0) {
            return false;
        }

        $this->groups->delete($id);

        return true;
    }
}
