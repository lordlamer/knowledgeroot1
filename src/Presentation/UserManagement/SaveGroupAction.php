<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\UserManagement;

use Knowledgeroot\Application\UserManagement\CreateGroup;
use Knowledgeroot\Application\UserManagement\InvalidUserData;
use Knowledgeroot\Application\UserManagement\UpdateGroup;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

/**
 * Handles the group form submit, for create (POST /groups/new) as well
 * as update (POST /groups/{id}).
 */
class SaveGroupAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly CreateGroup $createGroup,
        private readonly UpdateGroup $updateGroup,
        private readonly LegacySession $session,
        private readonly Translator $translator,
        private readonly UrlHelper $url,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $body = (array) $request->getParsedBody();
        $name = trim((string) ($body['name'] ?? ''));
        $id = isset($args['id']) ? (int) $args['id'] : null;

        try {
            if ($id === null) {
                $this->createGroup->execute($name);
                $this->session->flash($this->translator->_('Group was created!'));
            } else {
                $this->updateGroup->execute($id, $name);
                $this->session->flash($this->translator->_('Group was saved!'));
            }
        } catch (InvalidUserData $e) {
            $response->getBody()->write($this->twig->render('groups/form.html', [
                'mode' => $id === null ? 'add' : 'edit',
                'error' => $this->translator->_($e->getMessage()),
                'group_id' => $id,
                'name' => $name,
            ]));

            return $response->withStatus(422);
        }

        return $response->withHeader('Location', $this->url->to('users'))->withStatus(302);
    }
}
