<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Content;

class FileDownload
{
    public function __construct(
        public readonly int $fileId,
        public readonly int $pageId,
        public readonly string $filename,
        public readonly string $mimeType,
        public readonly string $bytes,
    ) {
    }
}
