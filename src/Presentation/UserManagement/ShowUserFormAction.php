<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\UserManagement;

use Knowledgeroot\Application\UserManagement\GetUserDetails;
use Knowledgeroot\Domain\Group\GroupRepository;
use Knowledgeroot\Infrastructure\Theme\ThemeLocator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

/**
 * Renders the user form, for /users/new as well as /users/{id}/edit.
 */
class ShowUserFormAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly GetUserDetails $getUserDetails,
        private readonly GroupRepository $groups,
        private readonly ThemeLocator $themes,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $details = null;
        if (isset($args['id'])) {
            $details = $this->getUserDetails->execute((int) $args['id']);

            if ($details === null) {
                return $response->withStatus(404);
            }
        }

        $response->getBody()->write($this->twig->render('users/form.html', [
            'mode' => $details === null ? 'add' : 'edit',
            'error' => null,
            'form' => $details === null ? null : [
                'id' => $details->user->id,
                'name' => $details->user->name,
                'theme' => $details->user->theme,
                'enabled' => $details->user->enabled,
                'defaultgroup' => $details->user->defaultGroup,
                'admin' => $details->user->admin,
                'rightedit' => $details->user->rightEdit,
                'userrights' => (int) substr(str_pad((string) $details->user->defaultRights, 3, '0', STR_PAD_LEFT), 0, 1),
                'grouprights' => (int) substr(str_pad((string) $details->user->defaultRights, 3, '0', STR_PAD_LEFT), 1, 1),
                'otherrights' => (int) substr(str_pad((string) $details->user->defaultRights, 3, '0', STR_PAD_LEFT), 2, 1),
                'groups' => $details->groupIds,
            ],
            'all_groups' => $this->groups->findAll(),
            'themes' => $this->themes->themeNames(),
        ]));

        return $response;
    }
}
