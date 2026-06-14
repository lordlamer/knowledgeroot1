<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\UserManagement;

use Knowledgeroot\Domain\User\User;

class UserDetails
{
    /**
     * @param int[] $groupIds
     */
    public function __construct(
        public readonly User $user,
        public readonly array $groupIds,
    ) {
    }
}
