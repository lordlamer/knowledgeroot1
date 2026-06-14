<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\FileHandling;

use Knowledgeroot\Domain\Content\AttachmentRepository;
use Knowledgeroot\Domain\Content\ContentAccess;
use Knowledgeroot\Domain\Content\ContentRepository;

/**
 * Use case: attach an uploaded file to a content block. Requires write
 * rights on the content block.
 */
class UploadFile
{
    public function __construct(
        private readonly AttachmentRepository $attachments,
        private readonly ContentRepository $contents,
        private readonly ContentAccess $contentAccess,
        private readonly int $maxFileSize,
    ) {
    }

    /**
     * @return int the page id to return to
     * @throws AccessDenied
     * @throws FileTooLarge
     * @throws FileError
     */
    public function execute(int $contentId, int $userId, UploadedFileData $file): int
    {
        $content = $this->contents->findEditable($contentId);
        if ($content === null) {
            throw new FileError('Content not found!');
        }

        if ($this->contentAccess->rights($contentId, $userId) !== 2) {
            throw new AccessDenied('You are not allowed to add files here!');
        }

        if ($this->maxFileSize > 0 && $file->size > $this->maxFileSize) {
            throw new FileTooLarge('Cannot add file. File is to big!');
        }

        if ($file->size === 0 || $file->filename === '') {
            throw new FileError('No file was uploaded!');
        }

        $this->attachments->store($contentId, $file->filename, $file->mimeType, $file->size, $file->bytes, $userId);

        return $content->pageId;
    }
}
