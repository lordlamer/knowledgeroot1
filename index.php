<?php
/**
 * Knowledgeroot is published under the GNU GPL! Read LICENSE
 *
 * Front controller: every request runs through the Slim app. Each use
 * case has its own route; unknown paths return a 404.
 *
 * @package Knowledgeroot
 */

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

// container with the new-style service definitions
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/config/dependencies.php');
AppFactory::setContainer($containerBuilder->build());

$app = AppFactory::create();
$app->addRoutingMiddleware();

// set KR_DEBUG=1 in the environment to see error details during development
$app->addErrorMiddleware((bool) getenv('KR_DEBUG'), true, true);

// support installations in a sub directory (e.g. /knowledgeroot/index.php)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($scriptDir !== '/' && $scriptDir !== '') {
	$app->setBasePath($scriptDir);
}

// --- new routes go here ---------------------------------------------------

$app->get('/ping', function (Request $request, Response $response) {
	$response->getBody()->write('pong');
	return $response;
});

// front page (replaces the legacy index.php home flow)
$app->get('/', \Knowledgeroot\Presentation\Home\HomeAction::class);
$app->get('/index.php', \Knowledgeroot\Presentation\Home\HomeAction::class);
$app->post('/language', \Knowledgeroot\Presentation\Home\LanguageSwitchAction::class);

$app->get('/search', \Knowledgeroot\Presentation\Search\SearchAction::class);

$app->get('/page/{id:[0-9]+}', \Knowledgeroot\Presentation\PageView\ViewPageAction::class);

$app->get('/content/{id:[0-9]+}/print', \Knowledgeroot\Presentation\PrintView\PrintContentAction::class);

$app->get('/file/{id:[0-9]+}', \Knowledgeroot\Presentation\FileHandling\DownloadFileAction::class);
$app->post('/content/{id:[0-9]+}/files', \Knowledgeroot\Presentation\FileHandling\UploadFileAction::class);
$app->post('/file/{id:[0-9]+}/delete', \Knowledgeroot\Presentation\FileHandling\DeleteFileAction::class);

$app->get('/page/root/new', \Knowledgeroot\Presentation\PageEditing\PageEditorAction::class);
$app->post('/page/root/new', \Knowledgeroot\Presentation\PageEditing\SavePageAction::class);
$app->get('/page/{parentId:[0-9]+}/subpage/new', \Knowledgeroot\Presentation\PageEditing\PageEditorAction::class);
$app->post('/page/{parentId:[0-9]+}/subpage/new', \Knowledgeroot\Presentation\PageEditing\SavePageAction::class);
$app->get('/page/{id:[0-9]+}/edit', \Knowledgeroot\Presentation\PageEditing\PageEditorAction::class);
$app->post('/page/{id:[0-9]+}/edit', \Knowledgeroot\Presentation\PageEditing\SavePageAction::class);
$app->post('/page/{id:[0-9]+}/delete', \Knowledgeroot\Presentation\PageEditing\DeletePageAction::class);

$app->get('/page/{pageId:[0-9]+}/content/new', \Knowledgeroot\Presentation\ContentEditing\ContentEditorAction::class);
$app->post('/page/{pageId:[0-9]+}/content/new', \Knowledgeroot\Presentation\ContentEditing\SaveContentAction::class);
$app->get('/content/{id:[0-9]+}/edit', \Knowledgeroot\Presentation\ContentEditing\ContentEditorAction::class);
$app->post('/content/{id:[0-9]+}/edit', \Knowledgeroot\Presentation\ContentEditing\SaveContentAction::class);
$app->post('/content/{id:[0-9]+}/delete', \Knowledgeroot\Presentation\ContentEditing\DeleteContentAction::class);
$app->post('/content/{id:[0-9]+}/move/{direction:up|down}', \Knowledgeroot\Presentation\ContentEditing\MoveContentAction::class);
$app->post('/content/{id:[0-9]+}/relocate', \Knowledgeroot\Presentation\ContentEditing\RelocateContentAction::class);
$app->post('/page/{id:[0-9]+}/relocate', \Knowledgeroot\Presentation\PageEditing\RelocatePageAction::class);

$app->get('/login', \Knowledgeroot\Presentation\Login\ShowLoginFormAction::class);
$app->post('/login', \Knowledgeroot\Presentation\Login\SubmitLoginAction::class);
$app->get('/logout', \Knowledgeroot\Presentation\Login\LogoutAction::class);

$app->group('', function (\Slim\Routing\RouteCollectorProxy $group) {
	$group->get('/options', \Knowledgeroot\Presentation\Options\ShowOptionsAction::class);
	$group->post('/options', \Knowledgeroot\Presentation\Options\SaveOptionsAction::class);
})->add(\Knowledgeroot\Presentation\Middleware\RequireLogin::class);

$app->group('', function (\Slim\Routing\RouteCollectorProxy $group) {
	$group->get('/users', \Knowledgeroot\Presentation\UserManagement\ListUsersAction::class);
	$group->get('/users/new', \Knowledgeroot\Presentation\UserManagement\ShowUserFormAction::class);
	$group->post('/users/new', \Knowledgeroot\Presentation\UserManagement\SaveUserAction::class);
	$group->get('/users/{id:[0-9]+}/edit', \Knowledgeroot\Presentation\UserManagement\ShowUserFormAction::class);
	$group->post('/users/{id:[0-9]+}', \Knowledgeroot\Presentation\UserManagement\SaveUserAction::class);
	$group->post('/users/{id:[0-9]+}/delete', \Knowledgeroot\Presentation\UserManagement\DeleteUserAction::class);

	$group->get('/groups/new', \Knowledgeroot\Presentation\UserManagement\ShowGroupFormAction::class);
	$group->post('/groups/new', \Knowledgeroot\Presentation\UserManagement\SaveGroupAction::class);
	$group->get('/groups/{id:[0-9]+}/edit', \Knowledgeroot\Presentation\UserManagement\ShowGroupFormAction::class);
	$group->post('/groups/{id:[0-9]+}', \Knowledgeroot\Presentation\UserManagement\SaveGroupAction::class);
	$group->post('/groups/{id:[0-9]+}/delete', \Knowledgeroot\Presentation\UserManagement\DeleteGroupAction::class);
})->add(\Knowledgeroot\Presentation\Middleware\RequireAdmin::class);

// --- admin backend (break-glass loginhash auth) ---------------------------
$app->get('/admin/login', [\Knowledgeroot\Presentation\Admin\AdminLoginAction::class, 'show']);
$app->post('/admin/login', [\Knowledgeroot\Presentation\Admin\AdminLoginAction::class, 'submit']);
$app->get('/admin/logout', \Knowledgeroot\Presentation\Admin\AdminLogoutAction::class);

$app->group('/admin', function (\Slim\Routing\RouteCollectorProxy $group) {
	$group->get('', \Knowledgeroot\Presentation\Admin\DashboardAction::class);
	$group->get('/config', \Knowledgeroot\Presentation\Admin\ConfigSectionAction::class);
	$group->get('/info', \Knowledgeroot\Presentation\Admin\InfoSectionAction::class);
	$group->get('/recover', [\Knowledgeroot\Presentation\Admin\RecoverSectionAction::class, 'show']);
	$group->post('/recover/reset', [\Knowledgeroot\Presentation\Admin\RecoverSectionAction::class, 'reset']);
	$group->post('/recover/create', [\Knowledgeroot\Presentation\Admin\RecoverSectionAction::class, 'create']);
})->add(\Knowledgeroot\Presentation\Admin\Middleware\RequireAdminGate::class);

$app->run();
