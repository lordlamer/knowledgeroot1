<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\PageView;

use Knowledgeroot\Domain\Page\Breadcrumb;
use Knowledgeroot\Domain\Page\Page;

class PageView
{
    /**
     * @param Breadcrumb[] $breadcrumb
     * @param ViewedContentBlock[] $blocks
     */
    public function __construct(
        public readonly Page $page,
        public readonly array $breadcrumb,
        public readonly array $blocks,
        public readonly bool $canEditPage,
    ) {
    }
}
