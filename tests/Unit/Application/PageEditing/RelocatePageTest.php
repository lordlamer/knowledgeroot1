<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Application\PageEditing;

use Knowledgeroot\Application\PageEditing\AccessDenied;
use Knowledgeroot\Application\PageEditing\InvalidPageData;
use Knowledgeroot\Application\PageEditing\RelocatePage;
use Knowledgeroot\Domain\Page\EditablePage;
use Knowledgeroot\Tests\Doubles\InMemoryPageRepository;
use PHPUnit\Framework\TestCase;

class RelocatePageTest extends TestCase
{
    private InMemoryPageRepository $pages;

    protected function setUp(): void
    {
        $this->pages = new InMemoryPageRepository();
        // tree: 1 (root) -> 2 -> 3 ; and 4 (root)
        $this->pages->store[1] = $this->page(1, 0);
        $this->pages->store[2] = $this->page(2, 1);
        $this->pages->store[3] = $this->page(3, 2);
        $this->pages->store[4] = $this->page(4, 0);
    }

    private function page(int $id, int $parent): EditablePage
    {
        return new EditablePage($id, $parent, 'p' . $id, '', 0, '', 0, false, 1, 0, 2, 2, 2);
    }

    private function service(array $rights): RelocatePage
    {
        return new RelocatePage($this->pages, new MapPageRights($rights));
    }

    public function testMovesPageUnderNewParent(): void
    {
        // move page 2 under page 4
        $this->service([2 => 2, 4 => 2])->execute(2, 4, 7, false);

        $this->assertSame(4, $this->pages->findEditable(2)->belongsTo);
    }

    public function testRejectsMoveIntoItself(): void
    {
        $this->expectException(InvalidPageData::class);
        $this->service([2 => 2])->execute(2, 2, 7, false);
    }

    public function testRejectsMoveIntoOwnSubtree(): void
    {
        // page 3 is a descendant of page 2 -> moving 2 under 3 is a cycle
        $this->expectException(InvalidPageData::class);
        $this->service([2 => 2, 3 => 2])->execute(2, 3, 7, false);
    }

    public function testRequiresWriteRightOnPageAndTarget(): void
    {
        $this->expectException(AccessDenied::class);
        $this->service([2 => 2, 4 => 1])->execute(2, 4, 7, false);
    }

    public function testRootMoveRequiresAdmin(): void
    {
        $this->expectException(AccessDenied::class);
        $this->service([2 => 2])->execute(2, 0, 7, false);
    }

    public function testAdminCanMoveToRoot(): void
    {
        $this->service([2 => 2])->execute(2, 0, 7, true);

        $this->assertSame(0, $this->pages->findEditable(2)->belongsTo);
    }
}
