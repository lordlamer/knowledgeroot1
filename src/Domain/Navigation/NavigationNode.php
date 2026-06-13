<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Navigation;

class NavigationNode
{
    /**
     * @param NavigationNode[] $children
     */
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $link,
        public readonly string $tooltip,
        public readonly bool $isSymlink,
        public readonly bool $active,
        public readonly bool $expanded,
        public readonly array $children,
    ) {
    }

    public function hasChildren(): bool
    {
        return $this->children !== [];
    }
}
