<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\Extension\Extension;
use Knowledgeroot\Domain\Extension\ExtensionRepository;

class DbalExtensionRepository implements ExtensionRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function findAll(): array
    {
        $rows = $this->connection->fetchAllAssociative('SELECT keyname, active, admin, version FROM extensions ORDER BY keyname');

        $extensions = [];
        foreach ($rows as $row) {
            $extensions[(string) $row['keyname']] = new Extension(
                keyname: (string) $row['keyname'],
                active: (bool) $row['active'],
                admin: (bool) $row['admin'],
                version: (string) ($row['version'] ?? ''),
            );
        }

        return $extensions;
    }

    public function find(string $keyname): ?Extension
    {
        $row = $this->connection->fetchAssociative('SELECT keyname, active, admin, version FROM extensions WHERE keyname = ?', [$keyname]);

        if ($row === false) {
            return null;
        }

        return new Extension((string) $row['keyname'], (bool) $row['active'], (bool) $row['admin'], (string) ($row['version'] ?? ''));
    }

    public function setActive(string $keyname, bool $active): void
    {
        $this->connection->update('extensions', ['active' => (int) $active], ['keyname' => $keyname]);
    }

    public function register(string $keyname, bool $admin): void
    {
        $this->connection->insert('extensions', [
            'keyname' => $keyname,
            'active' => 0,
            'admin' => (int) $admin,
            'version' => '',
        ]);
    }

    public function remove(string $keyname): void
    {
        $this->connection->delete('extensions', ['keyname' => $keyname]);
    }
}
