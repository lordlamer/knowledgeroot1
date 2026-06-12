<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Page;

class Breadcrumb
{
    public function __construct(
        public readonly int $pageId,
        public readonly string $title,
    ) {
    }
}
