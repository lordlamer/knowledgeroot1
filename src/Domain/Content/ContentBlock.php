<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Content;

class ContentBlock
{
    public function __construct(
        public readonly int $id,
        public readonly int $pageId,
        public readonly string $title,
        public readonly string $type,
        public readonly string $html,
        public readonly string $lastUpdatedBy,
        public readonly string $lastUpdated,
        public readonly string $createDate,
    ) {
    }

    public function isText(): bool
    {
        return $this->type === '' || $this->type === 'text';
    }
}
