<?php

declare(strict_types=1);

/**
 * Doctrine Migrations CLI for Knowledgeroot.
 *
 * Usage:
 *   php bin/migrations.php migrate            # apply pending migrations
 *   php bin/migrations.php list
 *   php bin/migrations.php migrations:status
 *
 * The DBAL connection is taken from the application container, i.e. from
 * config/app.ini - so it targets the configured database (sqlite,
 * mysql/mariadb or postgresql).
 */

use DI\ContainerBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\ConsoleRunner;

require __DIR__ . '/../vendor/autoload.php';

if (!is_file(__DIR__ . '/../config/app.ini')) {
    fwrite(STDERR, "No config/app.ini found. Copy config/app.ini.dist and configure the database first.\n");
    exit(1);
}

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../config/dependencies.php');
$container = $containerBuilder->build();

/** @var Connection $connection */
$connection = $container->get(Connection::class);

$dependencyFactory = DependencyFactory::fromConnection(
    new PhpFile(__DIR__ . '/../migrations.php'),
    new ExistingConnection($connection)
);

ConsoleRunner::run([], $dependencyFactory);
