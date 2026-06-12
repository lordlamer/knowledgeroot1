<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Application\Search;

use Knowledgeroot\Application\Search\Search;
use Knowledgeroot\Domain\Page\Breadcrumb;
use Knowledgeroot\Domain\Page\PageAccess;
use Knowledgeroot\Domain\Page\PagePathResolver;
use Knowledgeroot\Domain\Search\SearchQuery;
use Knowledgeroot\Domain\Search\SearchQueryParser;
use Knowledgeroot\Domain\Search\SearchRepository;
use Knowledgeroot\Domain\User\User;
use Knowledgeroot\Tests\Doubles\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

class FakeSearchRepository implements SearchRepository
{
    public array $contentHits = [];
    public array $pageHits = [];
    public array $fileHits = [];
    public ?int $contentPageId = null;

    public function findContentPageId(int $contentId): ?int
    {
        return $this->contentPageId;
    }

    public function searchContent(SearchQuery $query, int $userId, int $defaultGroupId, array $groupIds, bool $isAdmin): array
    {
        return $this->contentHits;
    }

    public function searchPages(SearchQuery $query): array
    {
        return $this->pageHits;
    }

    public function searchFiles(SearchQuery $query): array
    {
        return $this->fileHits;
    }
}

class AllowListPageAccess implements PageAccess
{
    /** @param int[] $readablePages */
    public function __construct(private readonly array $readablePages)
    {
    }

    public function rights(int $pageId, int $userId): int
    {
        return in_array($pageId, $this->readablePages, true) ? 1 : 0;
    }

    public function canRead(int $pageId, int $userId): bool
    {
        return in_array($pageId, $this->readablePages, true);
    }
}

class FakePathResolver implements PagePathResolver
{
    public function pathTo(int $pageId): array
    {
        return [new Breadcrumb($pageId, 'Page ' . $pageId)];
    }
}

class SearchTest extends TestCase
{
    private FakeSearchRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new FakeSearchRepository();
    }

    private function search(array $readablePages): Search
    {
        return new Search(
            new SearchQueryParser(),
            $this->repository,
            new InMemoryUserRepository([new User(1, 'admin', md5('x'), 2, 210, true, true, true, '', '', '')]),
            new AllowListPageAccess($readablePages),
            new FakePathResolver(),
        );
    }

    public function testResultsAreFilteredByPagePermission(): void
    {
        $this->repository->contentHits = [
            ['id' => 10, 'pageId' => 1, 'title' => 'visible'],
            ['id' => 11, 'pageId' => 2, 'title' => 'hidden'],
        ];
        $this->repository->pageHits = [
            ['id' => 1, 'title' => 'visible page'],
            ['id' => 2, 'title' => 'hidden page'],
        ];

        $outcome = $this->search([1])->execute('foo', 1);

        $this->assertCount(1, $outcome->content);
        $this->assertSame('visible', $outcome->content[0]['title']);
        $this->assertCount(1, $outcome->pages);
        $this->assertSame(1, $outcome->pages[0]['pageId']);
    }

    public function testResultsCarryBreadcrumbPath(): void
    {
        $this->repository->contentHits = [['id' => 10, 'pageId' => 1, 'title' => 't']];

        $outcome = $this->search([1])->execute('foo', 1);

        $this->assertSame('Page 1', $outcome->content[0]['path'][0]->title);
    }

    public function testHashSyntaxJumpsToContent(): void
    {
        $this->repository->contentPageId = 5;

        $outcome = $this->search([5])->execute('#123', 1);

        $this->assertTrue($outcome->isJump());
        $this->assertSame(5, $outcome->jumpToPageId);
        $this->assertSame(123, $outcome->jumpToContentId);
    }

    public function testHashJumpRespectsPermissions(): void
    {
        $this->repository->contentPageId = 5;

        $outcome = $this->search([])->execute('#123', 1);

        $this->assertFalse($outcome->isJump());
    }

    public function testEmptyQueryGivesEmptyResults(): void
    {
        $this->repository->contentHits = [['id' => 10, 'pageId' => 1, 'title' => 't']];

        $outcome = $this->search([1])->execute('   ', 1);

        $this->assertFalse($outcome->isJump());
        $this->assertSame([], $outcome->content);
    }
}
