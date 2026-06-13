<?php

declare(strict_types=1);

namespace Knowledgeroot\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial Knowledgeroot schema. Replaces the per-database dumps/*.sql of
 * the legacy installer with one portable definition built via the DBAL
 * schema API, so it runs on sqlite, mysql/mariadb and postgresql alike.
 */
final class Version20240101000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial Knowledgeroot schema (tree, content, files, users, groups, rights, settings)';
    }

    public function up(Schema $schema): void
    {
        $tree = $this->createWithId($schema, 'tree');
        $tree->addColumn('belongs_to', 'integer', ['default' => 0]);
        $tree->addColumn('title', 'string', ['length' => 255, 'default' => '']);
        $tree->addColumn('tooltip', 'string', ['length' => 255, 'default' => '']);
        $tree->addColumn('icon', 'string', ['length' => 255, 'default' => '']);
        $tree->addColumn('alias', 'string', ['length' => 255, 'default' => '']);
        $tree->addColumn('contentcollapsed', 'integer', ['default' => 0]);
        $tree->addColumn('defaultcontentposition', 'integer', ['default' => 0]);
        $this->addRights($tree);
        foreach (['subinheritrights', 'subinheritrightseditable', 'subinheritrightsdisable', 'subinheritowner', 'subinheritgroup', 'subinherituserrights', 'subinheritgrouprights', 'subinheritotherrights', 'symlink', 'sorting', 'deleted'] as $col) {
            $tree->addColumn($col, 'integer', ['default' => 0]);
        }
        $tree->addIndex(['belongs_to'], 'tree_belongs_to_idx');

        $content = $this->createWithId($schema, 'content');
        $content->addColumn('title', 'string', ['length' => 255, 'default' => '']);
        $content->addColumn('belongs_to', 'integer', ['default' => 0]);
        $content->addColumn('content', 'text', ['notnull' => false]);
        $content->addColumn('type', 'string', ['length' => 64, 'default' => 'text']);
        $content->addColumn('sorting', 'integer', ['default' => 0]);
        $this->addRights($content);
        $content->addColumn('createdate', 'datetime', ['notnull' => false]);
        $content->addColumn('lastupdatedby', 'integer', ['default' => 0]);
        $content->addColumn('lastupdated', 'datetime', ['notnull' => false]);
        $content->addColumn('deleted', 'integer', ['default' => 0]);
        $content->addIndex(['belongs_to'], 'content_belongs_to_idx');

        $files = $this->createWithId($schema, 'files');
        $files->addColumn('belongs_to', 'integer', ['default' => 0]);
        $files->addColumn('file', 'text', ['notnull' => false]);
        $files->addColumn('filename', 'string', ['length' => 255, 'default' => '']);
        $files->addColumn('filesize', 'integer', ['default' => 0]);
        $files->addColumn('filetype', 'string', ['length' => 255, 'default' => 'application/octet-stream']);
        $files->addColumn('owner', 'integer', ['default' => 0]);
        $files->addColumn('date', 'datetime', ['notnull' => false]);
        $files->addColumn('counter', 'integer', ['default' => 0]);
        $files->addColumn('deleted', 'integer', ['default' => 0]);
        $files->addIndex(['belongs_to'], 'files_belongs_to_idx');

        $users = $this->createWithId($schema, 'users');
        $users->addColumn('name', 'string', ['length' => 255, 'default' => '']);
        $users->addColumn('password', 'string', ['length' => 255, 'default' => '']);
        $users->addColumn('theme', 'string', ['length' => 64, 'default' => '']);
        $users->addColumn('language', 'string', ['length' => 32, 'default' => '']);
        $users->addColumn('enabled', 'integer', ['default' => 0]);
        $users->addColumn('defaultgroup', 'integer', ['default' => 0]);
        $users->addColumn('defaultrights', 'string', ['length' => 8, 'default' => '211']);
        $users->addColumn('admin', 'integer', ['default' => 0]);
        $users->addColumn('rightedit', 'integer', ['default' => 0]);
        $users->addColumn('treecache', 'text', ['notnull' => false]);
        $users->addColumn('deleted', 'integer', ['default' => 0]);

        $groups = $this->createWithId($schema, 'groups');
        $groups->addColumn('name', 'string', ['length' => 255, 'default' => '']);
        $groups->addColumn('enabled', 'integer', ['default' => 0]);
        $groups->addColumn('deleted', 'integer', ['default' => 0]);

        $userGroup = $this->createWithId($schema, 'user_group');
        $userGroup->addColumn('userid', 'integer', ['default' => 0]);
        $userGroup->addColumn('groupid', 'integer', ['default' => 0]);

        $access = $this->createWithId($schema, 'access');
        $access->addColumn('table_name', 'string', ['length' => 32, 'default' => '']);
        $access->addColumn('belongs_to', 'integer', ['default' => 0]);
        $access->addColumn('owner_group', 'string', ['length' => 1, 'default' => '']);
        $access->addColumn('owner_group_id', 'integer', ['default' => 0]);
        $access->addColumn('rights', 'integer', ['default' => 0]);
        $access->addIndex(['table_name', 'belongs_to'], 'access_lookup_idx');

        $extensions = $this->createWithId($schema, 'extensions');
        $extensions->addColumn('keyname', 'string', ['length' => 128, 'default' => '']);
        $extensions->addColumn('active', 'integer', ['default' => 0]);
        $extensions->addColumn('admin', 'integer', ['default' => 0]);
        $extensions->addColumn('version', 'string', ['length' => 32, 'default' => '']);

        $settings = $this->createWithId($schema, 'settings');
        $settings->addColumn('name', 'string', ['length' => 128]);
        $settings->addColumn('value', 'text', ['notnull' => false]);
        $settings->addColumn('description', 'text', ['notnull' => false]);
        $settings->addColumn('selection', 'text', ['notnull' => false]);

        $contentOpen = $this->createWithId($schema, 'content_open');
        $contentOpen->addColumn('contentid', 'integer', ['default' => 0]);
        $contentOpen->addColumn('userid', 'string', ['length' => 64, 'default' => '']);
        $contentOpen->addColumn('opened', 'integer', ['default' => 0]);

        $usersLogin = $schema->createTable('users_login');
        $usersLogin->addColumn('usersid', 'integer', ['default' => 0]);
        $usersLogin->addColumn('login_trial', 'integer', ['default' => 0]);
        $usersLogin->addColumn('lasttrydate', 'integer', ['default' => 0]);
        $usersLogin->addColumn('session_id', 'string', ['length' => 64, 'default' => '']);
        $usersLogin->setPrimaryKey(['usersid']);
    }

    public function down(Schema $schema): void
    {
        foreach (['tree', 'content', 'files', 'users', 'groups', 'user_group', 'access', 'extensions', 'settings', 'content_open', 'users_login'] as $table) {
            if ($schema->hasTable($table)) {
                $schema->dropTable($table);
            }
        }
    }

    private function createWithId(Schema $schema, string $name): Table
    {
        $table = $schema->createTable($name);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->setPrimaryKey(['id']);

        return $table;
    }

    private function addRights(Table $table): void
    {
        $table->addColumn('owner', 'integer', ['default' => 0]);
        $table->addColumn('group', 'integer', ['default' => 0]);
        $table->addColumn('userrights', 'integer', ['default' => 0]);
        $table->addColumn('grouprights', 'integer', ['default' => 0]);
        $table->addColumn('otherrights', 'integer', ['default' => 0]);
    }
}
