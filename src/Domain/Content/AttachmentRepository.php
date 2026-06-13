<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Content;

interface AttachmentRepository
{
    /**
     * @return Attachment[]
     */
    public function findByContent(int $contentId): array;
}
