<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\Page\Page;
use Knowledgeroot\Domain\Page\PageRepository;

class DbalPageRepository implements PageRepository
{
    public function __construct(private readonly Connection $connection)
    {
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
}
