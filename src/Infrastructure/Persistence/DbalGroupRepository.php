<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\Group\Group;
use Knowledgeroot\Domain\Group\GroupRepository;

class DbalGroupRepository implements GroupRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function findAll(): array
    {
        $rows = $this->connection->fetchAllAssociative('SELECT id, name, enabled FROM groups ORDER BY name');

        return array_map($this->hydrate(...), $rows);
    }

    public function findById(int $id): ?Group
    {
        $row = $this->connection->fetchAssociative('SELECT id, name, enabled FROM groups WHERE id = ?', [$id]);

        return $row === false ? null : $this->hydrate($row);
    }

    public function add(string $name): int
    {
        $this->connection->insert('groups', ['name' => $name, 'enabled' => 1]);

        return (int) $this->connection->lastInsertId();
    }

    public function rename(int $id, string $name): void
    {
        $this->connection->update('groups', ['name' => $name], ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->connection->transactional(function () use ($id): void {
            $this->connection->delete('groups', ['id' => $id]);
            $this->connection->delete('user_group', ['groupid' => $id]);
        });
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Group
    {
        return new Group(
            id: (int) $row['id'],
            name: (string) $row['name'],
            enabled: (bool) $row['enabled'],
        );
    }
}
