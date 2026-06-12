<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\User\User;
use Knowledgeroot\Domain\User\UserRepository;

class DbalUserRepository implements UserRepository
{
    private const FIELDS = 'id, name, password, defaultgroup, defaultrights, admin, rightedit, enabled, treecache, theme, language';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function findEnabledByName(string $name): ?User
    {
        $row = $this->connection->fetchAssociative(
            'SELECT ' . self::FIELDS . ' FROM users WHERE name = ? AND enabled = 1',
            [$name]
        );

        return $row === false ? null : $this->hydrate($row);
    }

    public function findById(int $id): ?User
    {
        $row = $this->connection->fetchAssociative(
            'SELECT ' . self::FIELDS . ' FROM users WHERE id = ?',
            [$id]
        );

        return $row === false ? null : $this->hydrate($row);
    }

    public function findAll(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT ' . self::FIELDS . ' FROM users ORDER BY name'
        );

        return array_map($this->hydrate(...), $rows);
    }

    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $count = $this->connection->fetchOne('SELECT COUNT(*) FROM users WHERE name = ? AND id <> ?', [$name, $excludeId]);
        } else {
            $count = $this->connection->fetchOne('SELECT COUNT(*) FROM users WHERE name = ?', [$name]);
        }

        return (int) $count > 0;
    }

    public function add(User $user, array $groupIds): int
    {
        return $this->connection->transactional(function () use ($user, $groupIds): int {
            $this->connection->insert('users', [
                'name' => $user->name,
                'password' => $user->passwordHash,
                'theme' => $user->theme,
                'language' => $user->language,
                'enabled' => (int) $user->enabled,
                'defaultgroup' => $user->defaultGroup,
                'defaultrights' => $user->defaultRights,
                'admin' => (int) $user->admin,
                'rightedit' => (int) $user->rightEdit,
                'treecache' => $user->treeCache,
            ]);

            $id = (int) $this->connection->lastInsertId();
            $this->replaceGroups($id, $groupIds);

            return $id;
        });
    }

    public function update(User $user, array $groupIds): void
    {
        $this->connection->transactional(function () use ($user, $groupIds): void {
            $this->connection->update('users', [
                'name' => $user->name,
                'password' => $user->passwordHash,
                'theme' => $user->theme,
                'enabled' => (int) $user->enabled,
                'defaultgroup' => $user->defaultGroup,
                'defaultrights' => $user->defaultRights,
                'admin' => (int) $user->admin,
                'rightedit' => (int) $user->rightEdit,
            ], ['id' => $user->id]);

            $this->replaceGroups($user->id, $groupIds);
        });
    }

    public function delete(int $id): void
    {
        $this->connection->transactional(function () use ($id): void {
            $this->connection->delete('users', ['id' => $id]);
            $this->connection->delete('user_group', ['userid' => $id]);
        });
    }

    public function groupIdsOfUser(int $id): array
    {
        $ids = $this->connection->fetchFirstColumn('SELECT groupid FROM user_group WHERE userid = ?', [$id]);

        return array_map(intval(...), $ids);
    }

    public function countByDefaultGroup(int $groupId): int
    {
        return (int) $this->connection->fetchOne('SELECT COUNT(*) FROM users WHERE defaultgroup = ?', [$groupId]);
    }

    /**
     * @param int[] $groupIds
     */
    private function replaceGroups(int $userId, array $groupIds): void
    {
        $this->connection->delete('user_group', ['userid' => $userId]);

        foreach (array_unique($groupIds) as $groupId) {
            $this->connection->insert('user_group', ['userid' => $userId, 'groupid' => (int) $groupId]);
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): User
    {
        return new User(
            id: (int) $row['id'],
            name: (string) $row['name'],
            passwordHash: (string) $row['password'],
            defaultGroup: (int) $row['defaultgroup'],
            defaultRights: (int) $row['defaultrights'],
            admin: (bool) $row['admin'],
            rightEdit: (bool) $row['rightedit'],
            enabled: (bool) $row['enabled'],
            treeCache: (string) ($row['treecache'] ?? ''),
            theme: (string) ($row['theme'] ?? ''),
            language: (string) ($row['language'] ?? ''),
        );
    }
}
