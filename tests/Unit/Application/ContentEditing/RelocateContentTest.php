<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Application\ContentEditing;

use Knowledgeroot\Application\ContentEditing\AccessDenied;
use Knowledgeroot\Application\ContentEditing\ContentNotFound;
use Knowledgeroot\Application\ContentEditing\RelocateContent;
use Knowledgeroot\Domain\Content\EditableContent;
use Knowledgeroot\Tests\Doubles\InMemoryContentRepository;
use PHPUnit\Framework\TestCase;

class RelocateContentTest extends TestCase
{
    private InMemoryContentRepository $contents;

    protected function setUp(): void
    {
        $this->contents = new InMemoryContentRepository();
        $this->contents->store[10] = new EditableContent(10, 1, 't', 'h', 1, 0, 2, 2, 2);
    }

    public function testMovesContentToTargetPage(): void
    {
        // content rights 2 on block, page rights 2 on target page 3
        $relocate = new RelocateContent($this->contents, new MapContentRights([10 => 2]), new FixedPageAccess(2));

        $pageId = $relocate->execute(10, 3, 7);

        $this->assertSame(3, $pageId);
        $this->assertSame(3, $this->contents->findEditable(10)->pageId);
    }

    public function testRequiresWriteRightOnContentAndTarget(): void
    {
        // no write right on target page
        $relocate = new RelocateContent($this->contents, new MapContentRights([10 => 2]), new FixedPageAccess(1));

        $this->expectException(AccessDenied::class);
        $relocate->execute(10, 3, 7);
    }

    public function testUnknownContentThrows(): void
    {
        $relocate = new RelocateContent($this->contents, new MapContentRights([10 => 2]), new FixedPageAccess(2));

        $this->expectException(ContentNotFound::class);
        $relocate->execute(999, 3, 7);
    }
}
