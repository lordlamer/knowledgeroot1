<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\Content\ContentBlock;
use Knowledgeroot\Domain\Content\ContentRepository;
use Knowledgeroot\Domain\Content\EditableContent;

class DbalContentRepository implements ContentRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function findEditable(int $contentId): ?EditableContent
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, belongs_to, title, content, owner, ' . $this->connection->quoteIdentifier('group')
            . ', userrights, grouprights, otherrights FROM content WHERE id = ? AND deleted = 0',
            [$contentId]
        );

        if ($row === false) {
            return null;
        }

        return new EditableContent(
            id: (int) $row['id'],
            pageId: (int) $row['belongs_to'],
            title: (string) ($row['title'] ?? ''),
            html: (string) ($row['content'] ?? ''),
            owner: (int) $row['owner'],
            group: (int) $row['group'],
            userRights: (int) $row['userrights'],
            groupRights: (int) $row['grouprights'],
            otherRights: (int) $row['otherrights'],
        );
    }

    public function nextSorting(int $pageId): int
    {
        $max = $this->connection->fetchOne('SELECT MAX(sorting) FROM content WHERE belongs_to = ?', [$pageId]);

        return (int) $max + 1;
    }

    public function add(EditableContent $content, int $sorting, int $lastUpdatedBy): int
    {
        $now = date('Y-m-d H:i:s');

        $this->connection->insert('content', [
            'belongs_to' => $content->pageId,
            'sorting' => $sorting,
            'content' => $content->html,
            'title' => $content->title,
            'type' => 'text',
            'createdate' => $now,
            'lastupdated' => $now,
            'lastupdatedby' => $lastUpdatedBy,
            'owner' => $content->owner,
            $this->connection->quoteIdentifier('group') => $content->group,
            'userrights' => $content->userRights,
            'grouprights' => $content->groupRights,
            'otherrights' => $content->otherRights,
            'deleted' => 0,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(EditableContent $content, int $lastUpdatedBy, bool $withRights): void
    {
        $data = [
            'content' => $content->html,
            'title' => $content->title,
            'lastupdated' => date('Y-m-d H:i:s'),
            'lastupdatedby' => $lastUpdatedBy,
        ];

        if ($withRights) {
            $data['owner'] = $content->owner;
            $data[$this->connection->quoteIdentifier('group')] = $content->group;
            $data['userrights'] = $content->userRights;
            $data['grouprights'] = $content->groupRights;
            $data['otherrights'] = $content->otherRights;
        }

        $this->connection->update('content', $data, ['id' => $content->id]);
    }

    public function softDelete(int $contentId): void
    {
        $this->connection->transactional(function () use ($contentId): void {
            $this->connection->update('content', ['deleted' => 1], ['id' => $contentId]);
            $this->connection->update('files', ['deleted' => 1], ['belongs_to' => $contentId]);
        });
    }

    public function orderedIdsByPage(int $pageId): array
    {
        $ids = $this->connection->fetchFirstColumn(
            'SELECT id FROM content WHERE belongs_to = ? AND deleted = 0 ORDER BY sorting ASC, id ASC',
            [$pageId]
        );

        return array_map(intval(...), $ids);
    }

    public function setSorting(int $contentId, int $sorting): void
    {
        $this->connection->update('content', ['sorting' => $sorting], ['id' => $contentId]);
    }

    public function findByPage(int $pageId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT ct.id, ct.belongs_to, ct.content, ct.title, ct.type,
                    u.name AS lastupdatedby, ct.lastupdated, ct.createdate
             FROM content ct
             LEFT JOIN users u ON ct.lastupdatedby = u.id
             WHERE ct.belongs_to = ? AND ct.deleted = 0
             ORDER BY ct.sorting ASC',
            [$pageId]
        );

        return array_map(static fn (array $row) => new ContentBlock(
            id: (int) $row['id'],
            pageId: (int) $row['belongs_to'],
            title: (string) ($row['title'] ?? ''),
            type: (string) ($row['type'] ?? ''),
            html: (string) ($row['content'] ?? ''),
            lastUpdatedBy: (string) ($row['lastupdatedby'] ?? ''),
            lastUpdated: (string) ($row['lastupdated'] ?? ''),
            createDate: (string) ($row['createdate'] ?? ''),
        ), $rows);
    }
}
