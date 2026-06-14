<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\UserManagement;

/**
 * Input for creating or updating a user. The three rights values
 * (2 = read+write, 1 = read, 0 = none) are stored combined as a three
 * digit number user|group|others - same format as the legacy code.
 */
class UserFormData
{
    /**
     * @param int[] $groupIds
     */
    public function __construct(
        public readonly string $name,
        public readonly string $password,
        public readonly string $theme,
        public readonly bool $enabled,
        public readonly int $defaultGroup,
        public readonly bool $admin,
        public readonly bool $rightEdit,
        public readonly int $userRights,
        public readonly int $groupRights,
        public readonly int $otherRights,
        public readonly array $groupIds,
    ) {
    }

    public function defaultRights(): int
    {
        return (int) sprintf('%d%d%d', $this->userRights, $this->groupRights, $this->otherRights);
    }
}
