<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Content;

interface AttachmentRepository
{
    /**
     * @return Attachment[]
     */
    public function findByContent(int $contentId): array;

    /**
     * the decoded file plus the page it belongs to, for download
     */
    public function findDownload(int $fileId): ?FileDownload;

    public function incrementCounter(int $fileId): void;

    /**
     * the content id a file belongs to, or null when it does not exist
     */
    public function contentIdOf(int $fileId): ?int;

    /**
     * @return int id of the stored file
     */
    public function store(int $contentId, string $filename, string $mimeType, int $size, string $bytes, int $owner): int;

    public function softDelete(int $fileId): void;
}
