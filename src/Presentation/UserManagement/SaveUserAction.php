<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\UserManagement;

use Knowledgeroot\Application\UserManagement\CreateUser;
use Knowledgeroot\Application\UserManagement\InvalidUserData;
use Knowledgeroot\Application\UserManagement\UpdateUser;
use Knowledgeroot\Application\UserManagement\UserFormData;
use Knowledgeroot\Domain\Group\GroupRepository;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Theme\ThemeLocator;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

/**
 * Handles the user form submit, for create (POST /users/new) as well
 * as update (POST /users/{id}).
 */
class SaveUserAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly CreateUser $createUser,
        private readonly UpdateUser $updateUser,
        private readonly GroupRepository $groups,
        private readonly ThemeLocator $themes,
        private readonly LegacySession $session,
        private readonly Translator $translator,
        private readonly UrlHelper $url,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $body = (array) $request->getParsedBody();

        $data = new UserFormData(
            name: trim((string) ($body['name'] ?? '')),
            password: (string) ($body['password'] ?? ''),
            theme: (string) ($body['theme'] ?? ''),
            enabled: (bool) ($body['enabled'] ?? false),
            defaultGroup: (int) ($body['defaultgroup'] ?? 0),
            admin: (bool) ($body['admin'] ?? false),
            rightEdit: (bool) ($body['rightedit'] ?? false),
            userRights: (int) ($body['userrights'] ?? 0),
            groupRights: (int) ($body['grouprights'] ?? 0),
            otherRights: (int) ($body['otherrights'] ?? 0),
            groupIds: array_map(intval(...), (array) ($body['groups'] ?? [])),
        );

        $id = isset($args['id']) ? (int) $args['id'] : null;

        try {
            if ($id === null) {
                $this->createUser->execute($data);
                $this->session->flash($this->translator->_('User was created!'));
            } else {
                $this->updateUser->execute($id, $data);
                $this->session->flash($this->translator->_('User was saved!'));
            }
        } catch (InvalidUserData $e) {
            $response->getBody()->write($this->twig->render('users/form.html', [
                'mode' => $id === null ? 'add' : 'edit',
                'error' => $this->translator->_($e->getMessage()),
                'form' => [
                    'id' => $id,
                    'name' => $data->name,
                    'theme' => $data->theme,
                    'enabled' => $data->enabled,
                    'defaultgroup' => $data->defaultGroup,
                    'admin' => $data->admin,
                    'rightedit' => $data->rightEdit,
                    'userrights' => $data->userRights,
                    'grouprights' => $data->groupRights,
                    'otherrights' => $data->otherRights,
                    'groups' => $data->groupIds,
                ],
                'all_groups' => $this->groups->findAll(),
                'themes' => $this->themes->themeNames(),
            ]));

            return $response->withStatus(422);
        }

        return $response->withHeader('Location', $this->url->to('users'))->withStatus(302);
    }
}
