<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\ContentEditing;

use Knowledgeroot\Domain\Content\ContentAccess;
use Knowledgeroot\Domain\Content\ContentRepository;

/**
 * Use case: soft-delete a content block (and its attachments). Requires
 * write rights on the block.
 */
class DeleteContent
{
    public function __construct(
        private readonly ContentRepository $contents,
        private readonly ContentAccess $contentAccess,
    ) {
    }

    /**
     * @return int the page id the block belonged to
     * @throws ContentNotFound
     * @throws AccessDenied
     */
    public function execute(int $contentId, int $userId): int
    {
        $existing = $this->contents->findEditable($contentId);
        if ($existing === null) {
            throw new ContentNotFound('Content not found!');
        }

        if ($this->contentAccess->rights($contentId, $userId) !== 2) {
            throw new AccessDenied('You are not allowed to delete this content!');
        }

        $this->contents->softDelete($contentId);

        return $existing->pageId;
    }
}
