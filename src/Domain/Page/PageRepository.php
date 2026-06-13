<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Page;

interface PageRepository
{
    public function findById(int $pageId): ?Page;
}
