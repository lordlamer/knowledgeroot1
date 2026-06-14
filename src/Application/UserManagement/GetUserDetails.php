<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\UserManagement;

use Knowledgeroot\Domain\User\User;
use Knowledgeroot\Domain\User\UserRepository;

class GetUserDetails
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function execute(int $id): ?UserDetails
    {
        $user = $this->users->findById($id);
        if ($user === null) {
            return null;
        }

        return new UserDetails($user, $this->users->groupIdsOfUser($id));
    }
}
