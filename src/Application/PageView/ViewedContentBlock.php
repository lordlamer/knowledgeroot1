<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\PageView;

use Knowledgeroot\Domain\Content\Attachment;
use Knowledgeroot\Domain\Content\ContentBlock;

class ViewedContentBlock
{
    /**
     * @param Attachment[] $attachments
     */
    public function __construct(
        public readonly ContentBlock $block,
        public readonly string $html,
        public readonly bool $canEdit,
        public readonly array $attachments,
    ) {
    }
}
