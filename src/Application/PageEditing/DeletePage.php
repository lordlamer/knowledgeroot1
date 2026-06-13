<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\PageEditing;

use Knowledgeroot\Domain\Page\PageAccess;
use Knowledgeroot\Domain\Page\PageRepository;

/**
 * Use case: delete an empty page. Requires write rights. A page that
 * still has subpages or content is refused - cascading delete is not
 * offered (the legacy admin cascade is intentionally not carried over).
 */
class DeletePage
{
    public function __construct(
        private readonly PageRepository $pages,
        private readonly PageAccess $pageAccess,
    ) {
    }

    /**
     * @return int the parent page id (0 when it was a root page)
     * @throws PageNotFound
     * @throws AccessDenied
     * @throws PageNotEmpty
     */
    public function execute(int $pageId, int $userId): int
    {
        $page = $this->pages->findEditable($pageId);
        if ($page === null) {
            throw new PageNotFound('Page not found!');
        }

        if ($this->pageAccess->rights($pageId, $userId) !== 2) {
            throw new AccessDenied('You are not allowed to delete this page!');
        }

        if ($this->pages->hasChildren($pageId) || $this->pages->hasContent($pageId)) {
            throw new PageNotEmpty('Cannot delete page. Check if content is on the page!');
        }

        $this->pages->softDelete($pageId);

        return $page->belongsTo;
    }
}
