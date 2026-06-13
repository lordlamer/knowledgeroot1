<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\FileHandling;

use Knowledgeroot\Domain\Content\AttachmentRepository;
use Knowledgeroot\Domain\Content\FileDownload;
use Knowledgeroot\Domain\Page\PageAccess;

/**
 * Use case: fetch a file for download. The viewer must be allowed to
 * read the page the file's content belongs to. Each successful download
 * increments the file's counter, as in the legacy code.
 */
class DownloadFile
{
    public function __construct(
        private readonly AttachmentRepository $attachments,
        private readonly PageAccess $pageAccess,
    ) {
    }

    public function execute(int $fileId, int $userId): ?FileDownload
    {
        $download = $this->attachments->findDownload($fileId);
        if ($download === null) {
            return null;
        }

        if (!$this->pageAccess->canRead($download->pageId, $userId)) {
            return null;
        }

        $this->attachments->incrementCounter($fileId);

        return $download;
    }
}
