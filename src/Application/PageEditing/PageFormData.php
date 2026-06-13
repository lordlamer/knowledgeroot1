<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\PageEditing;

class PageFormData
{
    public function __construct(
        public readonly string $title,
        public readonly string $tooltip = '',
        public readonly int $symlink = 0,
        public readonly string $icon = '',
        public readonly int $defaultContentPosition = 0,
        public readonly bool $contentCollapsed = false,
        public readonly int $group = 0,
        public readonly int $userRights = 2,
        public readonly int $groupRights = 2,
        public readonly int $otherRights = 2,
    ) {
    }
}
