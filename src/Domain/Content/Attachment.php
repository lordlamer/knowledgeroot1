<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Content;

class Attachment
{
    public function __construct(
        public readonly int $id,
        public readonly int $contentId,
        public readonly string $filename,
        public readonly int $filesize,
        public readonly string $date,
    ) {
    }
}
