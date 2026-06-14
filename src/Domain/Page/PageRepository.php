<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Page;

interface PageRepository
{
    public function findById(int $pageId): ?Page;

    public function findEditable(int $pageId): ?EditablePage;

    /**
     * @return int id of the new page
     */
    public function add(EditablePage $page): int;

    /**
     * @param bool $withRights also persist owner/group/rights
     */
    public function update(EditablePage $page, bool $withRights): void;

    public function softDelete(int $pageId): void;

    public function hasChildren(int $pageId): bool;

    public function hasContent(int $pageId): bool;

    public function changeParent(int $pageId, int $newParentId): void;

    /**
     * true when $ancestorId is an ancestor of $pageId (walking belongs_to)
     */
    public function isAncestor(int $ancestorId, int $pageId): bool;
}
