<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Navigation;

interface TreeRepository
{
    /**
     * all non-deleted tree nodes, ordered for display
     *
     * @param string $orderBy 'sorting' or 'title'
     * @return TreeNode[]
     */
    public function allActive(string $orderBy): array;
}
