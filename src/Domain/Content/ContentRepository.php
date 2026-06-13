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

    public function findEditable(int $contentId): ?EditableContent;

    /**
     * next free sorting value on a page (max + 1)
     */
    public function nextSorting(int $pageId): int;

    /**
     * @return int id of the new content block
     */
    public function add(EditableContent $content, int $sorting, int $lastUpdatedBy): int;

    /**
     * @param bool $withRights also persist owner/group/rights, not just text
     */
    public function update(EditableContent $content, int $lastUpdatedBy, bool $withRights): void;

    /**
     * soft-delete the content block and its attachments
     */
    public function softDelete(int $contentId): void;

    /**
     * content block ids of a page in display order (sorting, then id)
     *
     * @return int[]
     */
    public function orderedIdsByPage(int $pageId): array;

    public function setSorting(int $contentId, int $sorting): void;
}
