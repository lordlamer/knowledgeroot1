<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\FileHandling;

class UploadedFileData
{
    public function __construct(
        public readonly string $filename,
        public readonly string $mimeType,
        public readonly int $size,
        public readonly string $bytes,
    ) {
    }
}
