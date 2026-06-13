<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Application\PageEditing;

use Knowledgeroot\Application\PageEditing\AccessDenied;
use Knowledgeroot\Application\PageEditing\CreatePage;
use Knowledgeroot\Application\PageEditing\DeletePage;
use Knowledgeroot\Application\PageEditing\InvalidPageData;
use Knowledgeroot\Application\PageEditing\PageFormData;
use Knowledgeroot\Application\PageEditing\PageNotEmpty;
use Knowledgeroot\Application\PageEditing\PageNotFound;
use Knowledgeroot\Application\PageEditing\UpdatePage;
use Knowledgeroot\Domain\Page\EditablePage;
use Knowledgeroot\Domain\Page\PageAccess;
use Knowledgeroot\Domain\User\User;
use Knowledgeroot\Tests\Doubles\InMemoryPageRepository;
use Knowledgeroot\Tests\Doubles\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

class MapPageRights implements PageAccess
{
    /** @param array<int, int> $rights */
    public function __construct(private array $rights)
    {
    }

    public function rights(int $pageId, int $userId): int
    {
        return $this->rights[$pageId] ?? 0;
    }

    public function canRead(int $pageId, int $userId): bool
    {
        return ($this->rights[$pageId] ?? 0) > 0;
    }
}

class PageEditingTest extends TestCase
{
    private InMemoryPageRepository $pages;

    protected function setUp(): void
    {
        $this->pages = new InMemoryPageRepository();
    }

    private function seed(int $id = 5, int $parent = 0): void
    {
        $this->pages->store[$id] = new EditablePage($id, $parent, 'Page', 'tip', 0, '', 0, false, owner: 9, group: 2, userRights: 2, groupRights: 1, otherRights: 1);
    }

    // --- create -----------------------------------------------------------

    public function testCreateChildRequiresParentWriteRight(): void
    {
        $create = new CreatePage($this->pages, new MapPageRights([5 => 1]), new InMemoryUserRepository());

        $this->expectException(AccessDenied::class);
        $create->execute(5, 7, false, false, new PageFormData('New'));
    }

    public function testCreateRootRequiresAdmin(): void
    {
        $create = new CreatePage($this->pages, new MapPageRights([]), new InMemoryUserRepository());

        $this->expectException(AccessDenied::class);
        $create->execute(0, 7, false, false, new PageFormData('Root'));
    }

    public function testAdminCreatesRoot(): void
    {
        $create = new CreatePage($this->pages, new MapPageRights([]), new InMemoryUserRepository());

        $id = $create->execute(0, 7, true, true, new PageFormData('Root', group: 3, userRights: 2, groupRights: 1, otherRights: 0));

        $page = $this->pages->findEditable($id);
        $this->assertSame(0, $page->belongsTo);
        $this->assertSame('Root', $page->title);
        $this->assertSame([2, 1, 0], [$page->userRights, $page->groupRights, $page->otherRights]);
    }

    public function testCreateRejectsEmptyTitle(): void
    {
        $create = new CreatePage($this->pages, new MapPageRights([5 => 2]), new InMemoryUserRepository());

        $this->expectException(InvalidPageData::class);
        $create->execute(5, 7, false, true, new PageFormData('   '));
    }

    public function testNonPrivilegedChildGetsUserDefaults(): void
    {
        $users = new InMemoryUserRepository([
            new User(7, 'u', 'x', defaultGroup: 4, defaultRights: 210, admin: false, rightEdit: false, enabled: true, treeCache: '', theme: '', language: ''),
        ]);
        $create = new CreatePage($this->pages, new MapPageRights([5 => 2]), $users);

        $id = $create->execute(5, 7, false, false, new PageFormData('Child', group: 99, userRights: 0));

        $page = $this->pages->findEditable($id);
        $this->assertSame(4, $page->group);
        $this->assertSame([2, 1, 0], [$page->userRights, $page->groupRights, $page->otherRights]);
        $this->assertSame(7, $page->owner);
    }

    // --- update -----------------------------------------------------------

    public function testUpdateRequiresWriteRight(): void
    {
        $this->seed(5);
        $update = new UpdatePage($this->pages, new MapPageRights([5 => 1]));

        $this->expectException(AccessDenied::class);
        $update->execute(5, 7, true, new PageFormData('X'));
    }

    public function testUpdateUnknownPageThrows(): void
    {
        $update = new UpdatePage($this->pages, new MapPageRights([9 => 2]));

        $this->expectException(PageNotFound::class);
        $update->execute(9, 7, true, new PageFormData('X'));
    }

    public function testNonPrivilegedUpdateKeepsRights(): void
    {
        $this->seed(5);
        $update = new UpdatePage($this->pages, new MapPageRights([5 => 2]));

        $update->execute(5, 7, false, new PageFormData('Renamed', group: 99, userRights: 0, groupRights: 0, otherRights: 0));

        $page = $this->pages->findEditable(5);
        $this->assertSame('Renamed', $page->title);
        $this->assertSame(2, $page->group);
        $this->assertSame([2, 1, 1], [$page->userRights, $page->groupRights, $page->otherRights]);
    }

    // --- delete -----------------------------------------------------------

    public function testDeleteRequiresWriteRight(): void
    {
        $this->seed(5);
        $delete = new DeletePage($this->pages, new MapPageRights([5 => 1]));

        $this->expectException(AccessDenied::class);
        $delete->execute(5, 7);
    }

    public function testDeleteRefusesPageWithChildren(): void
    {
        $this->seed(5);
        $this->pages->childCount[5] = 2;
        $delete = new DeletePage($this->pages, new MapPageRights([5 => 2]));

        $this->expectException(PageNotEmpty::class);
        $delete->execute(5, 7);
    }

    public function testDeleteRefusesPageWithContent(): void
    {
        $this->seed(5);
        $this->pages->contentCount[5] = 1;
        $delete = new DeletePage($this->pages, new MapPageRights([5 => 2]));

        $this->expectException(PageNotEmpty::class);
        $delete->execute(5, 7);
    }

    public function testDeleteEmptyPageReturnsParent(): void
    {
        $this->seed(5, parent: 3);
        $delete = new DeletePage($this->pages, new MapPageRights([5 => 2]));

        $parent = $delete->execute(5, 7);

        $this->assertSame(3, $parent);
        $this->assertNull($this->pages->findEditable(5));
    }
}
