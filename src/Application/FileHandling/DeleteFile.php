<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\FileHandling;

use Knowledgeroot\Domain\Content\AttachmentRepository;
use Knowledgeroot\Domain\Content\ContentAccess;
use Knowledgeroot\Domain\Content\ContentRepository;

/**
 * Use case: remove a file attachment. Requires write rights on the
 * content block the file belongs to.
 */
class DeleteFile
{
    public function __construct(
        private readonly AttachmentRepository $attachments,
        private readonly ContentRepository $contents,
        private readonly ContentAccess $contentAccess,
    ) {
    }

    /**
     * @return int the page id to return to
     * @throws FileError
     * @throws AccessDenied
     */
    public function execute(int $fileId, int $userId): int
    {
        $contentId = $this->attachments->contentIdOf($fileId);
        if ($contentId === null) {
            throw new FileError('File not found!');
        }

        if ($this->contentAccess->rights($contentId, $userId) !== 2) {
            throw new AccessDenied('You are not allowed to delete this file!');
        }

        $this->attachments->softDelete($fileId);

        return $this->contents->findEditable($contentId)?->pageId ?? 0;
    }
}
