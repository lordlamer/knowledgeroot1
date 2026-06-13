<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Application\PrintView;

use Knowledgeroot\Application\PrintView\PrintContent;
use Knowledgeroot\Domain\Content\ContentAccess;
use Knowledgeroot\Domain\Content\EditableContent;
use Knowledgeroot\Domain\Page\PageAccess;
use Knowledgeroot\Tests\Doubles\InMemoryContentRepository;
use PHPUnit\Framework\TestCase;

class PrintPageAccess implements PageAccess
{
    public function __construct(private readonly bool $readable)
    {
    }

    public function rights(int $pageId, int $userId): int
    {
        return $this->readable ? 1 : 0;
    }

    public function canRead(int $pageId, int $userId): bool
    {
        return $this->readable;
    }
}

class PrintContentRights implements ContentAccess
{
    public function __construct(private readonly int $rights)
    {
    }

    public function rights(int $contentId, int $userId): int
    {
        return $this->rights;
    }
}

class PrintContentTest extends TestCase
{
    private InMemoryContentRepository $contents;

    protected function setUp(): void
    {
        $this->contents = new InMemoryContentRepository();
        $this->contents->store[10] = new EditableContent(10, 1, 'Title', '<p>Body</p>', 1, 0, 2, 2, 2);
    }

    public function testReturnsContentWhenReadable(): void
    {
        $print = new PrintContent($this->contents, new PrintContentRights(1), new PrintPageAccess(true));

        $content = $print->execute(10, 5);

        $this->assertNotNull($content);
        $this->assertSame('Title', $content->title);
        $this->assertSame('<p>Body</p>', $content->html);
    }

    public function testNullWhenPageNotReadable(): void
    {
        $print = new PrintContent($this->contents, new PrintContentRights(2), new PrintPageAccess(false));

        $this->assertNull($print->execute(10, 5));
    }

    public function testNullWhenNoContentRight(): void
    {
        $print = new PrintContent($this->contents, new PrintContentRights(0), new PrintPageAccess(true));

        $this->assertNull($print->execute(10, 5));
    }

    public function testNullForUnknownContent(): void
    {
        $print = new PrintContent($this->contents, new PrintContentRights(2), new PrintPageAccess(true));

        $this->assertNull($print->execute(999, 5));
    }
}
