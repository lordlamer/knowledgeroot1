<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Doubles;

use Knowledgeroot\Domain\Content\ContentBlock;
use Knowledgeroot\Domain\Content\ContentRepository;
use Knowledgeroot\Domain\Content\EditableContent;

class InMemoryContentRepository implements ContentRepository
{
    /** @var array<int, EditableContent> */
    public array $store = [];

    /** @var array<int, bool> */
    public array $deleted = [];

    /** @var array<int, array{lastUpdatedBy: int, withRights: bool}> */
    public array $updates = [];

    private int $nextId = 1;

    public function findByPage(int $pageId): array
    {
        $blocks = [];
        foreach ($this->store as $c) {
            if ($c->pageId === $pageId && !isset($this->deleted[$c->id])) {
                $blocks[] = new ContentBlock($c->id, $c->pageId, $c->title, 'text', $c->html, '', '', '');
            }
        }

        return $blocks;
    }

    public function findEditable(int $contentId): ?EditableContent
    {
        if (isset($this->deleted[$contentId])) {
            return null;
        }

        return $this->store[$contentId] ?? null;
    }

    public function nextSorting(int $pageId): int
    {
        return count($this->store) + 1;
    }

    public function add(EditableContent $content, int $sorting, int $lastUpdatedBy): int
    {
        $id = $this->nextId++;
        $this->store[$id] = new EditableContent(
            $id,
            $content->pageId,
            $content->title,
            $content->html,
            $content->owner,
            $content->group,
            $content->userRights,
            $content->groupRights,
            $content->otherRights,
        );

        return $id;
    }

    public function update(EditableContent $content, int $lastUpdatedBy, bool $withRights): void
    {
        $this->store[$content->id] = $content;
        $this->updates[$content->id] = ['lastUpdatedBy' => $lastUpdatedBy, 'withRights' => $withRights];
    }

    public function softDelete(int $contentId): void
    {
        $this->deleted[$contentId] = true;
    }

    /** @var array<int, int> content id => sorting */
    public array $sorting = [];

    public function orderedIdsByPage(int $pageId): array
    {
        $ids = [];
        foreach ($this->store as $c) {
            if ($c->pageId === $pageId && !isset($this->deleted[$c->id])) {
                $ids[$c->id] = $this->sorting[$c->id] ?? $c->id;
            }
        }
        asort($ids);

        return array_keys($ids);
    }

    public function setSorting(int $contentId, int $sorting): void
    {
        $this->sorting[$contentId] = $sorting;
    }

    public function moveToPage(int $contentId, int $targetPageId): void
    {
        $c = $this->store[$contentId];
        $this->store[$contentId] = new EditableContent($c->id, $targetPageId, $c->title, $c->html, $c->owner, $c->group, $c->userRights, $c->groupRights, $c->otherRights);
    }
}
