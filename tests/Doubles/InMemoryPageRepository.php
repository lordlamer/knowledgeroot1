<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Doubles;

use Knowledgeroot\Domain\Page\EditablePage;
use Knowledgeroot\Domain\Page\Page;
use Knowledgeroot\Domain\Page\PageRepository;

class InMemoryPageRepository implements PageRepository
{
    /** @var array<int, EditablePage> */
    public array $store = [];

    /** @var array<int, bool> */
    public array $deleted = [];

    /** @var array<int, int> page id => child count */
    public array $childCount = [];

    /** @var array<int, int> page id => content count */
    public array $contentCount = [];

    private int $nextId = 100;

    public function findById(int $pageId): ?Page
    {
        $p = $this->store[$pageId] ?? null;
        if ($p === null || isset($this->deleted[$pageId])) {
            return null;
        }

        return new Page($p->id, $p->belongsTo, $p->title, $p->contentCollapsed);
    }

    public function findEditable(int $pageId): ?EditablePage
    {
        if (isset($this->deleted[$pageId])) {
            return null;
        }

        return $this->store[$pageId] ?? null;
    }

    public function add(EditablePage $page): int
    {
        $id = $this->nextId++;
        $this->store[$id] = new EditablePage(
            $id,
            $page->belongsTo,
            $page->title,
            $page->tooltip,
            $page->symlink,
            $page->icon,
            $page->defaultContentPosition,
            $page->contentCollapsed,
            $page->owner,
            $page->group,
            $page->userRights,
            $page->groupRights,
            $page->otherRights,
        );

        return $id;
    }

    public function update(EditablePage $page, bool $withRights): void
    {
        $this->store[$page->id] = $page;
    }

    public function softDelete(int $pageId): void
    {
        $this->deleted[$pageId] = true;
    }

    public function hasChildren(int $pageId): bool
    {
        return ($this->childCount[$pageId] ?? 0) > 0;
    }

    public function hasContent(int $pageId): bool
    {
        return ($this->contentCount[$pageId] ?? 0) > 0;
    }
}
