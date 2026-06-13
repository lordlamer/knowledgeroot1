<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Page;

class Page
{
    public function __construct(
        public readonly int $id,
        public readonly int $belongsTo,
        public readonly string $title,
        public readonly bool $contentCollapsed,
    ) {
    }
}
