<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\ContentEditing;

class ContentFormData
{
    public function __construct(
        public readonly string $title,
        public readonly string $html,
        public readonly int $group = 0,
        public readonly int $userRights = 2,
        public readonly int $groupRights = 2,
        public readonly int $otherRights = 2,
    ) {
    }
}
