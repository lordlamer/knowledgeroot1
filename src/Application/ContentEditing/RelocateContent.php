<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\ContentEditing;

use Knowledgeroot\Domain\Content\ContentAccess;
use Knowledgeroot\Domain\Content\ContentRepository;
use Knowledgeroot\Domain\Page\PageAccess;

/**
 * Use case: move a content block to another page. Requires write rights
 * on the block and on the target page. The block is appended at the end
 * of the target page.
 */
class RelocateContent
{
    public function __construct(
        private readonly ContentRepository $contents,
        private readonly ContentAccess $contentAccess,
        private readonly PageAccess $pageAccess,
    ) {
    }

    /**
     * @return int the target page id
     * @throws ContentNotFound
     * @throws AccessDenied
     */
    public function execute(int $contentId, int $targetPageId, int $userId): int
    {
        $content = $this->contents->findEditable($contentId);
        if ($content === null) {
            throw new ContentNotFound('Content not found!');
        }

        if ($this->contentAccess->rights($contentId, $userId) !== 2 || $this->pageAccess->rights($targetPageId, $userId) !== 2) {
            throw new AccessDenied('You are not allowed to move this content!');
        }

        if ($targetPageId !== $content->pageId) {
            $this->contents->moveToPage($contentId, $targetPageId);
        }

        return $targetPageId;
    }
}
