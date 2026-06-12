<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Page;

interface PagePathResolver
{
    /**
     * @return Breadcrumb[] path from the root page down to the page
     */
    public function pathTo(int $pageId): array;
}
