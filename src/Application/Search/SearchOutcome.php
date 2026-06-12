<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\Search;

use Knowledgeroot\Domain\Page\Breadcrumb;

class SearchOutcome
{
    /**
     * @param array<int, array{contentId: int, pageId: int, title: string, path: Breadcrumb[]}> $content
     * @param array<int, array{pageId: int, path: Breadcrumb[]}> $pages
     * @param array<int, array{pageId: int, filename: string, filesize: int, date: string, path: Breadcrumb[]}> $files
     */
    private function __construct(
        public readonly ?int $jumpToPageId,
        public readonly ?int $jumpToContentId,
        public readonly array $content,
        public readonly array $pages,
        public readonly array $files,
    ) {
    }

    public static function jump(int $pageId, int $contentId): self
    {
        return new self($pageId, $contentId, [], [], []);
    }

    /**
     * @param array<int, array{contentId: int, pageId: int, title: string, path: Breadcrumb[]}> $content
     * @param array<int, array{pageId: int, path: Breadcrumb[]}> $pages
     * @param array<int, array{pageId: int, filename: string, filesize: int, date: string, path: Breadcrumb[]}> $files
     */
    public static function results(array $content, array $pages, array $files): self
    {
        return new self(null, null, $content, $pages, $files);
    }

    public function isJump(): bool
    {
        return $this->jumpToPageId !== null;
    }
}
