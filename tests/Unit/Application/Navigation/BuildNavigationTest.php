<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Application\Navigation;

use Knowledgeroot\Application\Navigation\BuildNavigation;
use Knowledgeroot\Domain\Navigation\NavigationTreeBuilder;
use Knowledgeroot\Domain\Navigation\TreeNode;
use Knowledgeroot\Domain\Navigation\TreeRepository;
use Knowledgeroot\Domain\Page\PageAccess;
use PHPUnit\Framework\TestCase;

class StaticTreeRepository implements TreeRepository
{
    /** @param TreeNode[] $nodes */
    public function __construct(private readonly array $nodes)
    {
    }

    public function allActive(string $orderBy): array
    {
        return $this->nodes;
    }
}

class ReadablePagesAccess implements PageAccess
{
    /** @param int[] $readable */
    public function __construct(private readonly array $readable)
    {
    }

    public function rights(int $pageId, int $userId): int
    {
        return in_array($pageId, $this->readable, true) ? 1 : 0;
    }

    public function canRead(int $pageId, int $userId): bool
    {
        return in_array($pageId, $this->readable, true);
    }
}

class BuildNavigationTest extends TestCase
{
    public function testOnlyReadablePagesAppearInTheTree(): void
    {
        $nodes = [
            new TreeNode(1, 0, 'Root', '', 0, ''),
            new TreeNode(2, 1, 'Readable', '', 0, ''),
            new TreeNode(3, 1, 'Secret', '', 0, ''),
        ];

        $navigation = new BuildNavigation(
            new StaticTreeRepository($nodes),
            new ReadablePagesAccess([1, 2]),
            new NavigationTreeBuilder(),
            'title',
        );

        $roots = $navigation->execute(userId: 7, activeId: 2);

        $this->assertCount(1, $roots);
        $this->assertCount(1, $roots[0]->children);
        $this->assertSame('Readable', $roots[0]->children[0]->title);
        $this->assertTrue($roots[0]->children[0]->active);
    }
}
