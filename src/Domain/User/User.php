<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\User;

class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $passwordHash,
        public readonly int $defaultGroup,
        public readonly int $defaultRights,
        public readonly bool $admin,
        public readonly bool $rightEdit,
        public readonly bool $enabled,
        public readonly string $treeCache,
        public readonly string $theme,
        public readonly string $language,
    ) {
    }
}
