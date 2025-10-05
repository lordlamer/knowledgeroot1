<?php

/**
 * Dependency Injection Container Configuration
 *
 * @package Knowledgeroot
 */

declare(strict_types=1);

use DI\Container;
use Psr\Container\ContainerInterface;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Laminas\Config\Reader\Ini as IniReader;
use Laminas\Config\Config;
use Laminas\I18n\Translator\Translator;
use Laminas\Cache\StorageFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extra\Intl\IntlExtension;

return function (Container $container) {
    // Configuration
    $container->set('config', function () {
        $reader = new IniReader();
        $configArray = $reader->fromFile(BASE_PATH . '/config/app.ini');
        return new Config($configArray, true);
    });

    // Database Connection (Doctrine DBAL)
    $container->set(Connection::class, function (ContainerInterface $c) {
        $config = $c->get('config');

        return DriverManager::getConnection([
            'driver' => $config->db->adapter,
            'host' => $config->db->params->host,
            'user' => $config->db->params->username,
            'password' => $config->db->params->password,
            'dbname' => $config->db->params->dbname,
            'charset' => $config->db->encoding ?? 'utf8mb4',
        ]);
    });

    // Cache
    $container->set('cache', function (ContainerInterface $c) {
        $config = $c->get('config');

        return StorageFactory::factory([
            'adapter' => [
                'name' => 'filesystem',
                'options' => [
                    'cache_dir' => BASE_PATH . '/' . $config->cache->path,
                    'ttl' => 3600,
                ],
            ],
        ]);
    });

    // Translator (i18n)
    $container->set(Translator::class, function (ContainerInterface $c) {
        $config = $c->get('config');
        $cache = $c->get('cache');

        $translator = new Translator();
        $translator->setCache($cache);

        // Load default locale
        $locale = $config->base->locale ?? 'en_US';
        $moFile = BASE_PATH . "/system/language/{$locale}.UTF8/LC_MESSAGES/knowledgeroot.mo";

        if (is_file($moFile)) {
            $translator->addTranslationFile('gettext', $moFile, 'default', $locale);
            $translator->setLocale($locale);
        }

        return $translator;
    });

    // Session Manager
    $container->set(
        \Knowledgeroot\Infrastructure\Session\SessionManager::class,
        \DI\autowire(\Knowledgeroot\Infrastructure\Session\SessionManager::class)
    );

    // Twig Template Engine
    $container->set(Environment::class, function (ContainerInterface $c) {
        $config = $c->get('config');

        $loader = new FilesystemLoader([
            BASE_PATH . '/templates',
            BASE_PATH . '/system/templates',
        ]);

        $twig = new Environment($loader, [
            'cache' => BASE_PATH . '/' . $config->cache->path,
            'auto_reload' => true,
            'debug' => $config->development->debug ?? false,
        ]);

        // Add extensions
        $twig->addExtension(new IntlExtension());

        return $twig;
    });

    // Repositories
    $container->set(
        \Knowledgeroot\Domain\Content\Repository\ContentRepositoryInterface::class,
        \DI\autowire(\Knowledgeroot\Infrastructure\Persistence\Doctrine\ContentRepository::class)
    );

    $container->set(
        \Knowledgeroot\Domain\User\Repository\UserRepositoryInterface::class,
        \DI\autowire(\Knowledgeroot\Infrastructure\Persistence\Doctrine\UserRepository::class)
    );

    $container->set(
        \Knowledgeroot\Domain\Category\Repository\CategoryRepositoryInterface::class,
        \DI\autowire(\Knowledgeroot\Infrastructure\Persistence\Doctrine\CategoryRepository::class)
    );

    // Services
    $container->set(
        \Knowledgeroot\Domain\Content\Service\ContentService::class,
        \DI\autowire(\Knowledgeroot\Domain\Content\Service\ContentService::class)
    );

    $container->set(
        \Knowledgeroot\Domain\User\Service\UserService::class,
        \DI\autowire(\Knowledgeroot\Domain\User\Service\UserService::class)
    );

    $container->set(
        \Knowledgeroot\Domain\Category\Service\CategoryService::class,
        \DI\autowire(\Knowledgeroot\Domain\Category\Service\CategoryService::class)
    );

    // Use Cases
    $container->set(
        \Knowledgeroot\Application\UseCase\Content\ShowContentUseCase::class,
        \DI\autowire(\Knowledgeroot\Application\UseCase\Content\ShowContentUseCase::class)
    );

    $container->set(
        \Knowledgeroot\Application\UseCase\Content\ListContentByCategoryUseCase::class,
        \DI\autowire(\Knowledgeroot\Application\UseCase\Content\ListContentByCategoryUseCase::class)
    );

    $container->set(
        \Knowledgeroot\Application\UseCase\Auth\LoginUseCase::class,
        \DI\autowire(\Knowledgeroot\Application\UseCase\Auth\LoginUseCase::class)
    );

    $container->set(
        \Knowledgeroot\Application\UseCase\Category\ShowCategoryUseCase::class,
        \DI\autowire(\Knowledgeroot\Application\UseCase\Category\ShowCategoryUseCase::class)
    );

    $container->set(
        \Knowledgeroot\Application\UseCase\Category\GetCategoryTreeUseCase::class,
        \DI\autowire(\Knowledgeroot\Application\UseCase\Category\GetCategoryTreeUseCase::class)
    );

    $container->set(
        \Knowledgeroot\Application\UseCase\Home\ShowDashboardUseCase::class,
        \DI\autowire(\Knowledgeroot\Application\UseCase\Home\ShowDashboardUseCase::class)
    );

    // Middleware
    $container->set(
        \Knowledgeroot\Infrastructure\Middleware\AuthenticationMiddleware::class,
        \DI\autowire(\Knowledgeroot\Infrastructure\Middleware\AuthenticationMiddleware::class)
    );

    $container->set(
        \Knowledgeroot\Infrastructure\Middleware\TwigContextMiddleware::class,
        \DI\autowire(\Knowledgeroot\Infrastructure\Middleware\TwigContextMiddleware::class)
    );

    // Controllers
    $container->set(
        \Knowledgeroot\Application\Controller\HomeController::class,
        \DI\autowire(\Knowledgeroot\Application\Controller\HomeController::class)
    );

    $container->set(
        \Knowledgeroot\Application\Controller\ContentController::class,
        \DI\autowire(\Knowledgeroot\Application\Controller\ContentController::class)
    );

    $container->set(
        \Knowledgeroot\Application\Controller\UserController::class,
        \DI\autowire(\Knowledgeroot\Application\Controller\UserController::class)
    );

    $container->set(
        \Knowledgeroot\Application\Controller\CategoryController::class,
        \DI\autowire(\Knowledgeroot\Application\Controller\CategoryController::class)
    );
};
