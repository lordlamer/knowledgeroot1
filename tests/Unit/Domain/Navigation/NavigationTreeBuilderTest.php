<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Domain\Navigation;

use Knowledgeroot\Domain\Navigation\NavigationTreeBuilder;
use Knowledgeroot\Domain\Navigation\TreeNode;
use PHPUnit\Framework\TestCase;

class NavigationTreeBuilderTest extends TestCase
{
    private NavigationTreeBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new NavigationTreeBuilder();
    }

    /**
     * @param array<int, array{0: int, 1: string, 2?: int}> $defs id => [belongsTo, title, symlink]
     * @return TreeNode[]
     */
    private function nodes(array $defs): array
    {
        $nodes = [];
        foreach ($defs as $id => $def) {
            $nodes[] = new TreeNode($id, $def[0], $def[1], '', $def[2] ?? 0, '');
        }

        return $nodes;
    }

    /** @param TreeNode[] $nodes */
    private function readableAll(array $nodes): array
    {
        $set = [];
        foreach ($nodes as $node) {
            $set[$node->id] = true;
        }

        return $set;
    }

    public function testBuildsNestedStructure(): void
    {
        $nodes = $this->nodes([
            1 => [0, 'Root'],
            2 => [1, 'Child A'],
            3 => [1, 'Child B'],
            4 => [2, 'Grandchild'],
        ]);

        $roots = $this->builder->build($nodes, $this->readableAll($nodes), 0);

        $this->assertCount(1, $roots);
        $this->assertSame('Root', $roots[0]->title);
        $this->assertCount(2, $roots[0]->children);
        $this->assertSame('Child A', $roots[0]->children[0]->title);
        $this->assertCount(1, $roots[0]->children[0]->children);
        $this->assertSame('Grandchild', $roots[0]->children[0]->children[0]->title);
    }

    public function testUnreadableNodesAreOmitted(): void
    {
        $nodes = $this->nodes([
            1 => [0, 'Root'],
            2 => [1, 'Visible'],
            3 => [1, 'Hidden'],
        ]);

        $readable = [1 => true, 2 => true]; // 3 not readable

        $roots = $this->builder->build($nodes, $readable, 0);

        $this->assertCount(1, $roots[0]->children);
        $this->assertSame('Visible', $roots[0]->children[0]->title);
    }

    public function testActiveNodeAndAncestorsAreExpanded(): void
    {
        $nodes = $this->nodes([
            1 => [0, 'Root'],
            2 => [1, 'Branch'],
            3 => [2, 'Active'],
            4 => [1, 'Other'],
        ]);

        $roots = $this->builder->build($nodes, $this->readableAll($nodes), 3);

        $root = $roots[0];
        $branch = $root->children[0];
        $active = $branch->children[0];
        $other = $root->children[1];

        $this->assertTrue($root->expanded, 'root is ancestor of active');
        $this->assertTrue($branch->expanded, 'branch is ancestor of active');
        $this->assertTrue($active->active);
        $this->assertTrue($active->expanded);
        $this->assertFalse($other->expanded, 'unrelated branch stays collapsed');
        $this->assertFalse($other->active);
    }

    public function testSymlinkProducesSymlinkLinkAndFlag(): void
    {
        $nodes = $this->nodes([
            1 => [0, 'Root'],
            2 => [1, 'Link', 5],
        ]);

        $roots = $this->builder->build($nodes, $this->readableAll($nodes), 0);

        $link = $roots[0]->children[0];
        $this->assertTrue($link->isSymlink);
        $this->assertSame('page/5', $link->link);
    }

    public function testPlainNodeLinksToOwnId(): void
    {
        $nodes = $this->nodes([1 => [0, 'Root']]);

        $roots = $this->builder->build($nodes, $this->readableAll($nodes), 0);

        $this->assertSame('page/1', $roots[0]->link);
        $this->assertFalse($roots[0]->isSymlink);
    }

    public function testEmptyTitleFallback(): void
    {
        $nodes = $this->nodes([1 => [0, '']]);

        $roots = $this->builder->build($nodes, $this->readableAll($nodes), 0);

        $this->assertSame('[…]', $roots[0]->title);
    }
}
