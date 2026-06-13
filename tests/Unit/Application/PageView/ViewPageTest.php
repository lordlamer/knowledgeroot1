<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Application\PageView;

use Knowledgeroot\Application\PageView\ViewPage;
use Knowledgeroot\Domain\Content\Attachment;
use Knowledgeroot\Domain\Content\AttachmentRepository;
use Knowledgeroot\Domain\Content\ContentAccess;
use Knowledgeroot\Domain\Content\ContentBlock;
use Knowledgeroot\Domain\Content\ContentRepository;
use Knowledgeroot\Domain\Content\Highlighter;
use Knowledgeroot\Domain\Page\Breadcrumb;
use Knowledgeroot\Domain\Page\Page;
use Knowledgeroot\Domain\Page\PageAccess;
use Knowledgeroot\Domain\Page\PagePathResolver;
use Knowledgeroot\Domain\Page\PageRepository;
use PHPUnit\Framework\TestCase;

class FakePageRepository implements PageRepository
{
    public ?Page $page = null;

    public function findById(int $pageId): ?Page
    {
        return $this->page;
    }

    public function findEditable(int $pageId): ?\Knowledgeroot\Domain\Page\EditablePage
    {
        return null;
    }

    public function add(\Knowledgeroot\Domain\Page\EditablePage $page): int
    {
        return 0;
    }

    public function update(\Knowledgeroot\Domain\Page\EditablePage $page, bool $withRights): void
    {
    }

    public function softDelete(int $pageId): void
    {
    }

    public function hasChildren(int $pageId): bool
    {
        return false;
    }

    public function hasContent(int $pageId): bool
    {
        return false;
    }
}

class FakeContentRepository implements ContentRepository
{
    /** @var ContentBlock[] */
    public array $blocks = [];

    public function findByPage(int $pageId): array
    {
        return $this->blocks;
    }

    public function findEditable(int $contentId): ?\Knowledgeroot\Domain\Content\EditableContent
    {
        return null;
    }

    public function nextSorting(int $pageId): int
    {
        return 1;
    }

    public function add(\Knowledgeroot\Domain\Content\EditableContent $content, int $sorting, int $lastUpdatedBy): int
    {
        return 0;
    }

    public function update(\Knowledgeroot\Domain\Content\EditableContent $content, int $lastUpdatedBy, bool $withRights): void
    {
    }

    public function softDelete(int $contentId): void
    {
    }

    public function orderedIdsByPage(int $pageId): array
    {
        return [];
    }

    public function setSorting(int $contentId, int $sorting): void
    {
    }
}

class FakeAttachmentRepository implements AttachmentRepository
{
    /** @var array<int, Attachment[]> */
    public array $byContent = [];

    public function findByContent(int $contentId): array
    {
        return $this->byContent[$contentId] ?? [];
    }

    public function findDownload(int $fileId): ?\Knowledgeroot\Domain\Content\FileDownload
    {
        return null;
    }

    public function incrementCounter(int $fileId): void
    {
    }

    public function contentIdOf(int $fileId): ?int
    {
        return null;
    }

    public function store(int $contentId, string $filename, string $mimeType, int $size, string $bytes, int $owner): int
    {
        return 0;
    }

    public function softDelete(int $fileId): void
    {
    }
}

class ConfigurablePageAccess implements PageAccess
{
    public function __construct(
        private readonly bool $readable,
        private readonly int $rights,
    ) {
    }

    public function rights(int $pageId, int $userId): int
    {
        return $this->rights;
    }

    public function canRead(int $pageId, int $userId): bool
    {
        return $this->readable;
    }
}

class MapContentAccess implements ContentAccess
{
    /** @param array<int, int> $rightsByContent */
    public function __construct(private readonly array $rightsByContent)
    {
    }

    public function rights(int $contentId, int $userId): int
    {
        return $this->rightsByContent[$contentId] ?? 0;
    }
}

class FakePagePathResolver implements PagePathResolver
{
    public function pathTo(int $pageId): array
    {
        return [new Breadcrumb($pageId, 'Page ' . $pageId)];
    }
}

class ViewPageTest extends TestCase
{
    private FakePageRepository $pages;
    private FakeContentRepository $contents;
    private FakeAttachmentRepository $attachments;

    protected function setUp(): void
    {
        $this->pages = new FakePageRepository();
        $this->contents = new FakeContentRepository();
        $this->attachments = new FakeAttachmentRepository();
    }

    private function block(int $id, string $title = '', string $html = 'body', string $type = 'text'): ContentBlock
    {
        return new ContentBlock($id, 1, $title, $type, $html, 'admin', '2024-01-01', '2024-01-01');
    }

    private function viewPage(PageAccess $pageAccess, ContentAccess $contentAccess): ViewPage
    {
        return new ViewPage(
            $this->pages,
            $this->contents,
            $this->attachments,
            $pageAccess,
            $contentAccess,
            new FakePagePathResolver(),
            new Highlighter(),
        );
    }

    public function testReturnsNullForUnknownPage(): void
    {
        $this->pages->page = null;

        $result = $this->viewPage(new ConfigurablePageAccess(true, 2), new MapContentAccess([]))->execute(1, 5);

        $this->assertNull($result);
    }

    public function testReturnsNullWhenPageNotReadable(): void
    {
        $this->pages->page = new Page(1, 0, 'Home', false);

        $result = $this->viewPage(new ConfigurablePageAccess(false, 0), new MapContentAccess([]))->execute(1, 5);

        $this->assertNull($result);
    }

    public function testHidesContentBlocksWithoutReadRight(): void
    {
        $this->pages->page = new Page(1, 0, 'Home', false);
        $this->contents->blocks = [$this->block(10), $this->block(11)];

        $view = $this->viewPage(
            new ConfigurablePageAccess(true, 1),
            new MapContentAccess([10 => 1, 11 => 0]),
        )->execute(1, 5);

        $this->assertCount(1, $view->blocks);
        $this->assertSame(10, $view->blocks[0]->block->id);
    }

    public function testCanEditRequiresWriteOnBothPageAndContent(): void
    {
        $this->pages->page = new Page(1, 0, 'Home', false);
        $this->contents->blocks = [$this->block(10), $this->block(11)];

        // page rights only 1 -> no edit even if content right is 2
        $view = $this->viewPage(
            new ConfigurablePageAccess(true, 1),
            new MapContentAccess([10 => 2, 11 => 1]),
        )->execute(1, 5);

        $this->assertFalse($view->blocks[0]->canEdit);
        $this->assertFalse($view->canEditPage);

        // page rights 2 + content 2 -> editable
        $view = $this->viewPage(
            new ConfigurablePageAccess(true, 2),
            new MapContentAccess([10 => 2, 11 => 1]),
        )->execute(1, 5);

        $this->assertTrue($view->canEditPage);
        $this->assertTrue($view->blocks[0]->canEdit);
        $this->assertFalse($view->blocks[1]->canEdit);
    }

    public function testSkipsNonTextBlocks(): void
    {
        $this->pages->page = new Page(1, 0, 'Home', false);
        $this->contents->blocks = [$this->block(10, type: 'text'), $this->block(11, type: 'dojorte')];

        $view = $this->viewPage(
            new ConfigurablePageAccess(true, 1),
            new MapContentAccess([10 => 1, 11 => 1]),
        )->execute(1, 5);

        $this->assertCount(1, $view->blocks);
        $this->assertSame(10, $view->blocks[0]->block->id);
    }

    public function testAttachmentsAreLoadedForBlocks(): void
    {
        $this->pages->page = new Page(1, 0, 'Home', false);
        $this->contents->blocks = [$this->block(10)];
        $this->attachments->byContent[10] = [new Attachment(1, 10, 'file.pdf', 2048, '2024-01-01')];

        $view = $this->viewPage(
            new ConfigurablePageAccess(true, 1),
            new MapContentAccess([10 => 1]),
        )->execute(1, 5);

        $this->assertCount(1, $view->blocks[0]->attachments);
        $this->assertSame('file.pdf', $view->blocks[0]->attachments[0]->filename);
    }

    public function testHighlightTermsAreApplied(): void
    {
        $this->pages->page = new Page(1, 0, 'Home', false);
        $this->contents->blocks = [$this->block(10, html: 'the quick fox')];

        $view = $this->viewPage(
            new ConfigurablePageAccess(true, 1),
            new MapContentAccess([10 => 1]),
        )->execute(1, 5, ['quick']);

        $this->assertStringContainsString('<span class="highlightword">quick</span>', $view->blocks[0]->html);
    }

    public function testBreadcrumbIsResolved(): void
    {
        $this->pages->page = new Page(7, 0, 'Home', false);

        $view = $this->viewPage(new ConfigurablePageAccess(true, 1), new MapContentAccess([]))->execute(7, 5);

        $this->assertCount(1, $view->breadcrumb);
        $this->assertSame('Page 7', $view->breadcrumb[0]->title);
    }
}
