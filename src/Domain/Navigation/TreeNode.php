<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Navigation;

/**
 * A raw row of the page tree.
 */
class TreeNode
{
    public function __construct(
        public readonly int $id,
        public readonly int $belongsTo,
        public readonly string $title,
        public readonly string $tooltip,
        public readonly int $symlink,
        public readonly string $icon,
    ) {
    }
}
