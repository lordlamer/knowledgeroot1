<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\PageEditing;

use Knowledgeroot\Domain\Page\PageAccess;
use Knowledgeroot\Domain\Page\PageRepository;

/**
 * Use case: move a page under another parent. Requires write rights on
 * the page and on the target parent (an admin for a root move). A page
 * cannot be moved into itself or into its own subtree.
 */
class RelocatePage
{
    public function __construct(
        private readonly PageRepository $pages,
        private readonly PageAccess $pageAccess,
    ) {
    }

    /**
     * @return int the new parent id
     * @throws PageNotFound
     * @throws AccessDenied
     * @throws InvalidPageData
     */
    public function execute(int $pageId, int $targetParentId, int $userId, bool $isAdmin): int
    {
        $page = $this->pages->findEditable($pageId);
        if ($page === null) {
            throw new PageNotFound('Page not found!');
        }

        $targetWritable = $targetParentId === 0 ? $isAdmin : $this->pageAccess->rights($targetParentId, $userId) === 2;
        if ($this->pageAccess->rights($pageId, $userId) !== 2 || !$targetWritable) {
            throw new AccessDenied('You are not allowed to move this page!');
        }

        if ($targetParentId === $pageId || $this->pages->isAncestor($pageId, $targetParentId)) {
            throw new InvalidPageData('A page cannot be moved into itself!');
        }

        $this->pages->changeParent($pageId, $targetParentId);

        return $targetParentId;
    }
}
