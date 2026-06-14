<?php

declare(strict_types=1);

namespace Knowledgeroot\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Seed the two default groups (admin = 1, users = 2) so a fresh install
 * has the admin group the break-glass recovery assigns by default. The
 * first administrator is created afterwards via the admin backend
 * (/admin -> recover), so no password is seeded here.
 */
final class Version20240101000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed default groups (admin, users)';
    }

    public function up(Schema $schema): void
    {
        $groups = $this->connection->quoteIdentifier('groups');
        $this->addSql("INSERT INTO $groups (name, enabled, deleted) VALUES ('admin', 1, 0)");
        $this->addSql("INSERT INTO $groups (name, enabled, deleted) VALUES ('users', 1, 0)");
    }

    public function down(Schema $schema): void
    {
        $groups = $this->connection->quoteIdentifier('groups');
        $this->addSql("DELETE FROM $groups WHERE name IN ('admin', 'users')");
    }
}
