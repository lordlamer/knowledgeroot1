<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Content;

interface ContentRepository
{
    /**
     * content blocks of a page, ordered by their sorting
     *
     * @return ContentBlock[]
     */
    public function findByPage(int $pageId): array;
}
