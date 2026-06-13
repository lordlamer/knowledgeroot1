<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\Page\EditablePage;
use Knowledgeroot\Domain\Page\Page;
use Knowledgeroot\Domain\Page\PageRepository;

class DbalPageRepository implements PageRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    private function group(): string
    {
        return $this->connection->quoteIdentifier('group');
    }

    public function findById(int $pageId): ?Page
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, belongs_to, title, contentcollapsed FROM tree WHERE id = ? AND deleted = 0',
            [$pageId]
        );

        if ($row === false) {
            return null;
        }

        return new Page(
            id: (int) $row['id'],
            belongsTo: (int) $row['belongs_to'],
            title: (string) $row['title'],
            contentCollapsed: (bool) $row['contentcollapsed'],
        );
    }

    public function findEditable(int $pageId): ?EditablePage
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, belongs_to, title, tooltip, symlink, icon, defaultcontentposition, contentcollapsed, owner, '
            . $this->group() . ', userrights, grouprights, otherrights FROM tree WHERE id = ? AND deleted = 0',
            [$pageId]
        );

        if ($row === false) {
            return null;
        }

        return new EditablePage(
            id: (int) $row['id'],
            belongsTo: (int) $row['belongs_to'],
            title: (string) ($row['title'] ?? ''),
            tooltip: (string) ($row['tooltip'] ?? ''),
            symlink: (int) ($row['symlink'] ?? 0),
            icon: (string) ($row['icon'] ?? ''),
            defaultContentPosition: (int) ($row['defaultcontentposition'] ?? 0),
            contentCollapsed: (bool) $row['contentcollapsed'],
            owner: (int) $row['owner'],
            group: (int) $row['group'],
            userRights: (int) $row['userrights'],
            groupRights: (int) $row['grouprights'],
            otherRights: (int) $row['otherrights'],
        );
    }

    public function add(EditablePage $page): int
    {
        $this->connection->insert('tree', [
            'belongs_to' => $page->belongsTo,
            'title' => $page->title,
            'tooltip' => $page->tooltip,
            'symlink' => $page->symlink,
            'icon' => $page->icon,
            'defaultcontentposition' => $page->defaultContentPosition,
            'contentcollapsed' => (int) $page->contentCollapsed,
            'sorting' => 0,
            'owner' => $page->owner,
            $this->group() => $page->group,
            'userrights' => $page->userRights,
            'grouprights' => $page->groupRights,
            'otherrights' => $page->otherRights,
            'deleted' => 0,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(EditablePage $page, bool $withRights): void
    {
        $data = [
            'title' => $page->title,
            'tooltip' => $page->tooltip,
            'symlink' => $page->symlink,
            'icon' => $page->icon,
            'defaultcontentposition' => $page->defaultContentPosition,
            'contentcollapsed' => (int) $page->contentCollapsed,
        ];

        if ($withRights) {
            $data['owner'] = $page->owner;
            $data[$this->group()] = $page->group;
            $data['userrights'] = $page->userRights;
            $data['grouprights'] = $page->groupRights;
            $data['otherrights'] = $page->otherRights;
        }

        $this->connection->update('tree', $data, ['id' => $page->id]);
    }

    public function softDelete(int $pageId): void
    {
        $this->connection->update('tree', ['deleted' => 1], ['id' => $pageId]);
    }

    public function hasChildren(int $pageId): bool
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM tree WHERE belongs_to = ? AND deleted = 0',
            [$pageId]
        ) > 0;
    }

    public function hasContent(int $pageId): bool
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM content WHERE belongs_to = ? AND deleted = 0',
            [$pageId]
        ) > 0;
    }

    public function changeParent(int $pageId, int $newParentId): void
    {
        $this->connection->update('tree', ['belongs_to' => $newParentId], ['id' => $pageId]);
    }

    public function isAncestor(int $ancestorId, int $pageId): bool
    {
        $current = $pageId;
        $guard = 0;

        while ($current !== 0 && $guard++ < 100) {
            $parent = $this->connection->fetchOne('SELECT belongs_to FROM tree WHERE id = ? AND deleted = 0', [$current]);
            if ($parent === false) {
                return false;
            }
            $parent = (int) $parent;
            if ($parent === $ancestorId) {
                return true;
            }
            $current = $parent;
        }

        return false;
    }
}
