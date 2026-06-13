<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\PrintView;

use Knowledgeroot\Domain\Content\ContentAccess;
use Knowledgeroot\Domain\Content\ContentRepository;
use Knowledgeroot\Domain\Content\EditableContent;
use Knowledgeroot\Domain\Page\PageAccess;

/**
 * Use case: fetch a single content block for the print view. Read
 * access is enough (page readable and content rights >= 1).
 */
class PrintContent
{
    public function __construct(
        private readonly ContentRepository $contents,
        private readonly ContentAccess $contentAccess,
        private readonly PageAccess $pageAccess,
    ) {
    }

    public function execute(int $contentId, int $userId): ?EditableContent
    {
        $content = $this->contents->findEditable($contentId);
        if ($content === null) {
            return null;
        }

        if (!$this->pageAccess->canRead($content->pageId, $userId)) {
            return null;
        }

        if ($this->contentAccess->rights($contentId, $userId) < 1) {
            return null;
        }

        return $content;
    }
}
