<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\Content\ContentBlock;
use Knowledgeroot\Domain\Content\ContentRepository;

class DbalContentRepository implements ContentRepository
{
    public function __construct(private readonly Connection $connection)
    {
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
