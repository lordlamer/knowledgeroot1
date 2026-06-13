<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\Navigation;

use Knowledgeroot\Domain\Navigation\NavigationTreeBuilder;
use Knowledgeroot\Domain\Navigation\TreeRepository;
use Knowledgeroot\Domain\Page\PageAccess;

/**
 * Use case: build the navigation tree for a viewer, containing only the
 * pages they may read, with the branch to the active page expanded.
 */
class BuildNavigation
{
    public function __construct(
        private readonly TreeRepository $tree,
        private readonly PageAccess $access,
        private readonly NavigationTreeBuilder $builder,
        private readonly string $orderBy,
    ) {
    }

    /**
     * @return \Knowledgeroot\Domain\Navigation\NavigationNode[]
     */
    public function execute(int $userId, int $activeId = 0): array
    {
        $nodes = $this->tree->allActive($this->orderBy);

        $readable = [];
        foreach ($nodes as $node) {
            if ($this->access->canRead($node->id, $userId)) {
                $readable[$node->id] = true;
            }
        }

        return $this->builder->build($nodes, $readable, $activeId);
    }
}
