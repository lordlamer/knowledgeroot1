<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Navigation;

/**
 * Builds the nested navigation tree from the flat tree rows, keeping
 * only the nodes the viewer may read. The branch leading to the active
 * page is marked as expanded so the tree opens to the current page.
 *
 * Permission filtering is downward-closed: because a node is only
 * readable when all its ancestors are readable, dropping unreadable
 * nodes never orphans a readable child.
 */
class NavigationTreeBuilder
{
    /**
     * @param TreeNode[] $nodes ordered as they should appear
     * @param array<int, true> $readableIds set of node ids the viewer may read
     * @return NavigationNode[] root nodes (belongs_to = 0)
     */
    public function build(array $nodes, array $readableIds, int $activeId): array
    {
        /** @var array<int, TreeNode[]> $childrenOf */
        $childrenOf = [];
        /** @var array<int, TreeNode> $byId */
        $byId = [];

        foreach ($nodes as $node) {
            if (!isset($readableIds[$node->id])) {
                continue;
            }
            $childrenOf[$node->belongsTo][] = $node;
            $byId[$node->id] = $node;
        }

        $expandedIds = $this->ancestorPath($activeId, $byId);

        return $this->buildLevel(0, $childrenOf, $expandedIds, $activeId);
    }

    /**
     * @param array<int, TreeNode[]> $childrenOf
     * @param array<int, true> $expandedIds
     * @return NavigationNode[]
     */
    private function buildLevel(int $parentId, array $childrenOf, array $expandedIds, int $activeId): array
    {
        $result = [];

        foreach ($childrenOf[$parentId] ?? [] as $node) {
            $children = $this->buildLevel($node->id, $childrenOf, $expandedIds, $activeId);

            $result[] = new NavigationNode(
                id: $node->id,
                title: $node->title !== '' ? $node->title : '[…]',
                link: $node->symlink !== 0 ? 'page/' . $node->symlink : 'page/' . $node->id,
                tooltip: $node->tooltip,
                isSymlink: $node->symlink !== 0,
                active: $node->id === $activeId,
                expanded: isset($expandedIds[$node->id]),
                children: $children,
            );
        }

        return $result;
    }

    /**
     * ids of the active node and all its ancestors
     *
     * @param array<int, TreeNode> $byId
     * @return array<int, true>
     */
    private function ancestorPath(int $activeId, array $byId): array
    {
        $path = [];
        $current = $activeId;
        $guard = 0;

        while ($current !== 0 && isset($byId[$current]) && $guard++ < 100) {
            $path[$current] = true;
            $current = $byId[$current]->belongsTo;
        }

        return $path;
    }
}
