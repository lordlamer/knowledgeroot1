<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Application\ContentEditing;

use Knowledgeroot\Application\ContentEditing\AccessDenied;
use Knowledgeroot\Application\ContentEditing\ContentFormData;
use Knowledgeroot\Application\ContentEditing\ContentNotFound;
use Knowledgeroot\Application\ContentEditing\CreateContent;
use Knowledgeroot\Application\ContentEditing\DeleteContent;
use Knowledgeroot\Application\ContentEditing\UpdateContent;
use Knowledgeroot\Domain\Content\ContentAccess;
use Knowledgeroot\Domain\Content\EditableContent;
use Knowledgeroot\Domain\Page\PageAccess;
use Knowledgeroot\Domain\User\User;
use Knowledgeroot\Tests\Doubles\InMemoryContentRepository;
use Knowledgeroot\Tests\Doubles\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

class FixedPageAccess implements PageAccess
{
    public function __construct(private readonly int $rights)
    {
    }

    public function rights(int $pageId, int $userId): int
    {
        return $this->rights;
    }

    public function canRead(int $pageId, int $userId): bool
    {
        return $this->rights > 0;
    }
}

class MapContentRights implements ContentAccess
{
    /** @param array<int, int> $rights */
    public function __construct(private array $rights)
    {
    }

    public function rights(int $contentId, int $userId): int
    {
        return $this->rights[$contentId] ?? 0;
    }
}

class ContentEditingTest extends TestCase
{
    private InMemoryContentRepository $contents;

    protected function setUp(): void
    {
        $this->contents = new InMemoryContentRepository();
    }

    // --- create -----------------------------------------------------------

    public function testCreateRequiresPageWriteRight(): void
    {
        $create = new CreateContent($this->contents, new FixedPageAccess(1), new InMemoryUserRepository());

        $this->expectException(AccessDenied::class);
        $create->execute(1, 5, true, new ContentFormData('t', 'body'));
    }

    public function testPrivilegedEditorSetsExplicitRights(): void
    {
        $create = new CreateContent($this->contents, new FixedPageAccess(2), new InMemoryUserRepository());

        $id = $create->execute(1, 5, true, new ContentFormData('t', 'body', group: 3, userRights: 2, groupRights: 1, otherRights: 0));

        $stored = $this->contents->findEditable($id);
        $this->assertSame(5, $stored->owner);
        $this->assertSame(3, $stored->group);
        $this->assertSame([2, 1, 0], [$stored->userRights, $stored->groupRights, $stored->otherRights]);
    }

    public function testNonPrivilegedUserGetsDefaultGroupAndRights(): void
    {
        $users = new InMemoryUserRepository([
            new User(5, 'maria', 'x', defaultGroup: 7, defaultRights: 210, admin: false, rightEdit: false, enabled: true, treeCache: '', theme: '', language: ''),
        ]);
        $create = new CreateContent($this->contents, new FixedPageAccess(2), $users);

        // form rights are ignored for non-privileged users
        $id = $create->execute(1, 5, false, new ContentFormData('t', 'body', group: 99, userRights: 0, groupRights: 0, otherRights: 0));

        $stored = $this->contents->findEditable($id);
        $this->assertSame(7, $stored->group);
        $this->assertSame([2, 1, 0], [$stored->userRights, $stored->groupRights, $stored->otherRights]);
    }

    public function testGuestGetsPublicDefaultRights(): void
    {
        $create = new CreateContent($this->contents, new FixedPageAccess(2), new InMemoryUserRepository());

        $id = $create->execute(1, 0, false, new ContentFormData('t', 'body'));

        $stored = $this->contents->findEditable($id);
        $this->assertSame(0, $stored->owner);
        $this->assertSame([2, 2, 2], [$stored->userRights, $stored->groupRights, $stored->otherRights]);
    }

    // --- update -----------------------------------------------------------

    private function seed(int $id = 10, int $pageId = 1): void
    {
        $this->contents->store[$id] = new EditableContent($id, $pageId, 'old', 'old body', owner: 9, group: 2, userRights: 2, groupRights: 1, otherRights: 1);
    }

    public function testUpdateRequiresContentWriteRight(): void
    {
        $this->seed();
        $update = new UpdateContent($this->contents, new MapContentRights([10 => 1]));

        $this->expectException(AccessDenied::class);
        $update->execute(10, 5, true, new ContentFormData('new', 'new body'));
    }

    public function testUpdateUnknownContentThrows(): void
    {
        $update = new UpdateContent($this->contents, new MapContentRights([10 => 2]));

        $this->expectException(ContentNotFound::class);
        $update->execute(999, 5, true, new ContentFormData('x', 'y'));
    }

    public function testNonPrivilegedUpdateKeepsRightsAndOwner(): void
    {
        $this->seed();
        $update = new UpdateContent($this->contents, new MapContentRights([10 => 2]));

        $pageId = $update->execute(10, 5, false, new ContentFormData('new', 'new body', group: 99, userRights: 0, groupRights: 0, otherRights: 0));

        $this->assertSame(1, $pageId);
        $stored = $this->contents->findEditable(10);
        $this->assertSame('new', $stored->title);
        $this->assertSame('new body', $stored->html);
        // rights untouched
        $this->assertSame(2, $stored->group);
        $this->assertSame([2, 1, 1], [$stored->userRights, $stored->groupRights, $stored->otherRights]);
        $this->assertSame(9, $stored->owner);
        $this->assertFalse($this->contents->updates[10]['withRights']);
    }

    public function testPrivilegedUpdateChangesRightsButKeepsOwner(): void
    {
        $this->seed();
        $update = new UpdateContent($this->contents, new MapContentRights([10 => 2]));

        $update->execute(10, 5, true, new ContentFormData('new', 'body', group: 4, userRights: 1, groupRights: 1, otherRights: 0));

        $stored = $this->contents->findEditable(10);
        $this->assertSame(4, $stored->group);
        $this->assertSame([1, 1, 0], [$stored->userRights, $stored->groupRights, $stored->otherRights]);
        $this->assertSame(9, $stored->owner, 'owner is preserved');
        $this->assertTrue($this->contents->updates[10]['withRights']);
    }

    // --- delete -----------------------------------------------------------

    public function testDeleteRequiresContentWriteRight(): void
    {
        $this->seed();
        $delete = new DeleteContent($this->contents, new MapContentRights([10 => 1]));

        $this->expectException(AccessDenied::class);
        $delete->execute(10, 5);
    }

    public function testDeleteSoftDeletesAndReturnsPageId(): void
    {
        $this->seed(10, 3);
        $delete = new DeleteContent($this->contents, new MapContentRights([10 => 2]));

        $pageId = $delete->execute(10, 5);

        $this->assertSame(3, $pageId);
        $this->assertNull($this->contents->findEditable(10));
    }
}
