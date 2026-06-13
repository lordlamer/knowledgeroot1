<?php

declare(strict_types=1);

/**
 * PHP-DI service definitions for the new (non-legacy) code paths.
 *
 * The legacy application builds its own $CLASS array in include/init.php.
 * New use cases get their dependencies from this container instead; as the
 * migration proceeds, services move from init.php to here.
 */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Knowledgeroot\Application\Login\AuthenticateUser;
use Knowledgeroot\Domain\Content\AttachmentRepository;
use Knowledgeroot\Domain\Content\ContentAccess;
use Knowledgeroot\Domain\Content\ContentRepository;
use Knowledgeroot\Application\FileHandling\UploadFile;
use Knowledgeroot\Application\Navigation\BuildNavigation;
use Knowledgeroot\Domain\Group\GroupRepository;
use Knowledgeroot\Domain\Navigation\NavigationTreeBuilder;
use Knowledgeroot\Domain\Navigation\TreeRepository;
use Knowledgeroot\Domain\Page\PageAccess;
use Knowledgeroot\Domain\Page\PagePathResolver;
use Knowledgeroot\Domain\Page\PageRepository;
use Knowledgeroot\Domain\Search\SearchRepository;
use Knowledgeroot\Domain\User\LoginThrottleRepository;
use Knowledgeroot\Domain\User\UserRepository;
use Knowledgeroot\Infrastructure\Persistence\DbalAttachmentRepository;
use Knowledgeroot\Infrastructure\Persistence\DbalContentAccess;
use Knowledgeroot\Infrastructure\Persistence\DbalContentRepository;
use Knowledgeroot\Infrastructure\Persistence\DbalPageAccess;
use Knowledgeroot\Infrastructure\Persistence\DbalPageRepository;
use Knowledgeroot\Infrastructure\Persistence\DbalPagePathResolver;
use Knowledgeroot\Infrastructure\Persistence\DbalTreeRepository;
use Knowledgeroot\Infrastructure\Persistence\DbalSearchRepository;
use Knowledgeroot\Infrastructure\Persistence\DbalGroupRepository;
use Knowledgeroot\Infrastructure\Language\LanguageLocator;
use Knowledgeroot\Infrastructure\Theme\ThemeLocator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Knowledgeroot\Infrastructure\Cache\FileCache;
use Knowledgeroot\Infrastructure\Config\Config;
use Knowledgeroot\Infrastructure\Mail\MailerFactory;
use Knowledgeroot\Infrastructure\Persistence\DbalLoginThrottleRepository;
use Knowledgeroot\Infrastructure\Persistence\DbalUserRepository;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Infrastructure\Twig\I18nExtension;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use function DI\autowire;

$basePath = str_replace('\\', '/', dirname(__DIR__)) . '/';

return [
	'base_path' => $basePath,

	Config::class => fn (ContainerInterface $c) => Config::fromIniFile($c->get('base_path') . 'config/app.ini'),

	Connection::class => function (ContainerInterface $c) {
		$db = $c->get(Config::class)->db;

		$aliases = ['mysql' => 'pdo_mysql', 'pgsql' => 'pdo_pgsql', 'postgres' => 'pdo_pgsql', 'sqlite' => 'pdo_sqlite'];
		$driver = (string) $db->adapter;
		$driver = $aliases[$driver] ?? $driver;

		$params = [
			'driver' => $driver,
			'user' => (string) $db->params->username,
			'password' => (string) $db->params->password,
			'host' => (string) $db->params->host,
		];

		if ($driver === 'pdo_sqlite') {
			$path = (string) $db->params->dbname;
			// resolve relative sqlite paths against the project root so the
			// connection also works from cli (cwd independent)
			if (!preg_match('#^([a-zA-Z]:[/\\\\]|/)#', $path)) {
				$path = $c->get('base_path') . $path;
			}
			$params['path'] = $path;
		} else {
			$params['dbname'] = (string) $db->params->dbname;
		}

		return DriverManager::getConnection($params);
	},

	Translator::class => function (ContainerInterface $c) {
		$locale = (string) ($c->get(Config::class)->base->locale ?: 'en_US');

		$file = $c->get('base_path') . 'system/language/' . $locale . '.UTF8/LC_MESSAGES/knowledgeroot.mo';
		if (!is_file($file)) {
			$locale = 'en_US';
			$file = $c->get('base_path') . 'system/language/en_US.UTF8/LC_MESSAGES/knowledgeroot.mo';
		}

		return new Translator($file, $locale);
	},

	Environment::class => function (ContainerInterface $c) {
		$config = $c->get(Config::class);

		$twig = new Environment(new FilesystemLoader($c->get('base_path') . 'system/templates'), [
			'cache' => $c->get('base_path') . $config->cache->path,
			'auto_reload' => true,
		]);
		$twig->addExtension(new I18nExtension($c->get(Translator::class)));
		$twig->addGlobal('site_title', (string) $config->base->title);
		$twig->addGlobal('base_url', $c->get(UrlHelper::class)->to(''));

		return $twig;
	},

	ThemeLocator::class => fn (ContainerInterface $c) => new ThemeLocator($c->get('base_path') . 'system/themes/'),

	LanguageLocator::class => fn (ContainerInterface $c) => new LanguageLocator($c->get('base_path') . 'system/language/'),

	FileCache::class => function (ContainerInterface $c) {
		$config = $c->get(Config::class);

		return new FileCache(
			$c->get('base_path') . $config->cache->path,
			(bool) $config->cache->options->caching,
			(int) $config->cache->options->lifetime
		);
	},

	MailerInterface::class => fn (ContainerInterface $c) => MailerFactory::fromConfig($c->get(Config::class)->email),

	// repositories
	UserRepository::class => autowire(DbalUserRepository::class),
	LoginThrottleRepository::class => autowire(DbalLoginThrottleRepository::class),
	GroupRepository::class => autowire(DbalGroupRepository::class),
	SearchRepository::class => autowire(DbalSearchRepository::class),
	PageAccess::class => autowire(DbalPageAccess::class),
	PagePathResolver::class => autowire(DbalPagePathResolver::class),
	PageRepository::class => autowire(DbalPageRepository::class),
	ContentRepository::class => autowire(DbalContentRepository::class),
	ContentAccess::class => autowire(DbalContentAccess::class),
	AttachmentRepository::class => autowire(DbalAttachmentRepository::class),
	TreeRepository::class => autowire(DbalTreeRepository::class),

	UploadFile::class => fn (ContainerInterface $c) => new UploadFile(
		$c->get(AttachmentRepository::class),
		$c->get(ContentRepository::class),
		$c->get(ContentAccess::class),
		(int) ($c->get(Config::class)->upload->maxfilesize ?: 0),
	),

	BuildNavigation::class => fn (ContainerInterface $c) => new BuildNavigation(
		$c->get(TreeRepository::class),
		$c->get(PageAccess::class),
		$c->get(NavigationTreeBuilder::class),
		(string) $c->get(Config::class)->tree->order === 'self' ? 'sorting' : 'title',
	),

	// use cases
	AuthenticateUser::class => function (ContainerInterface $c) {
		$login = $c->get(Config::class)->login;

		return new AuthenticateUser(
			$c->get(UserRepository::class),
			$c->get(LoginThrottleRepository::class),
			(int) ($login->delay ?: 30),
			(int) ($login->max ?: 50)
		);
	},
];
