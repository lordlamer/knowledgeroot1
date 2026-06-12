<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\UserManagement;

use Knowledgeroot\Domain\User\UserRepository;

class DeleteUser
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    /**
     * @param int $currentUserId the acting admin - deleting your own account is not allowed
     */
    public function execute(int $id, int $currentUserId): bool
    {
        if ($id === $currentUserId || $this->users->findById($id) === null) {
            return false;
        }

        $this->users->delete($id);

        return true;
    }
}
