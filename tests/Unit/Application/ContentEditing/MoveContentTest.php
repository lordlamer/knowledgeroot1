<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Application\ContentEditing;

use Knowledgeroot\Application\ContentEditing\AccessDenied;
use Knowledgeroot\Application\ContentEditing\ContentNotFound;
use Knowledgeroot\Application\ContentEditing\MoveContent;
use Knowledgeroot\Domain\Content\EditableContent;
use Knowledgeroot\Domain\Page\PageAccess;
use Knowledgeroot\Tests\Doubles\InMemoryContentRepository;
use PHPUnit\Framework\TestCase;

class FixedPageRights implements PageAccess
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

class MoveContentTest extends TestCase
{
    private InMemoryContentRepository $contents;

    protected function setUp(): void
    {
        $this->contents = new InMemoryContentRepository();
        // three blocks on page 1 in order 10, 11, 12
        foreach ([10, 11, 12] as $i => $id) {
            $this->contents->store[$id] = new EditableContent($id, 1, 'b' . $id, '', 1, 0, 2, 2, 2);
            $this->contents->sorting[$id] = $i;
        }
    }

    private function order(): array
    {
        return $this->contents->orderedIdsByPage(1);
    }

    public function testMoveUpSwapsWithPredecessor(): void
    {
        $move = new MoveContent($this->contents, new FixedPageRights(2));

        $move->execute(11, 5, MoveContent::UP);

        $this->assertSame([11, 10, 12], $this->order());
    }

    public function testMoveDownSwapsWithSuccessor(): void
    {
        $move = new MoveContent($this->contents, new FixedPageRights(2));

        $move->execute(11, 5, MoveContent::DOWN);

        $this->assertSame([10, 12, 11], $this->order());
    }

    public function testMoveUpAtTopIsNoop(): void
    {
        $move = new MoveContent($this->contents, new FixedPageRights(2));

        $move->execute(10, 5, MoveContent::UP);

        $this->assertSame([10, 11, 12], $this->order());
    }

    public function testMoveDownAtBottomIsNoop(): void
    {
        $move = new MoveContent($this->contents, new FixedPageRights(2));

        $move->execute(12, 5, MoveContent::DOWN);

        $this->assertSame([10, 11, 12], $this->order());
    }

    public function testReturnsPageId(): void
    {
        $move = new MoveContent($this->contents, new FixedPageRights(2));

        $this->assertSame(1, $move->execute(11, 5, MoveContent::UP));
    }

    public function testRequiresPageWriteRight(): void
    {
        $move = new MoveContent($this->contents, new FixedPageRights(1));

        $this->expectException(AccessDenied::class);
        $move->execute(11, 5, MoveContent::UP);
    }

    public function testUnknownContentThrows(): void
    {
        $move = new MoveContent($this->contents, new FixedPageRights(2));

        $this->expectException(ContentNotFound::class);
        $move->execute(999, 5, MoveContent::UP);
    }
}
