<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\Navigation\TreeNode;
use Knowledgeroot\Domain\Navigation\TreeRepository;

class DbalTreeRepository implements TreeRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function allActive(string $orderBy): array
    {
        // whitelist the order column - it is not a bound parameter
        $column = $orderBy === 'sorting' ? 'sorting' : 'title';

        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, belongs_to, title, tooltip, symlink, icon FROM tree WHERE deleted = 0 ORDER BY ' . $column . ' ASC'
        );

        return array_map(static fn (array $row) => new TreeNode(
            id: (int) $row['id'],
            belongsTo: (int) $row['belongs_to'],
            title: (string) ($row['title'] ?? ''),
            tooltip: (string) ($row['tooltip'] ?? ''),
            symlink: (int) ($row['symlink'] ?? 0),
            icon: (string) ($row['icon'] ?? ''),
        ), $rows);
    }
}
