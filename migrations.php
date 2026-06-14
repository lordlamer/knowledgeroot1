<?php

declare(strict_types=1);

/**
 * doctrine/migrations configuration. The connection is wired in
 * bin/migrations.php from the application's DBAL connection (app.ini),
 * so migrations run against whichever database is configured.
 */

return [
    'table_storage' => [
        'table_name' => 'doctrine_migration_versions',
    ],
    'migrations_paths' => [
        'Knowledgeroot\\Migrations' => __DIR__ . '/migrations',
    ],
    'all_or_nothing' => true,
    'transactional' => true,
    'check_database_platform' => false,
];
