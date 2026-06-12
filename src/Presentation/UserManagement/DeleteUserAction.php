<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\UserManagement;

use Knowledgeroot\Application\UserManagement\DeleteUser;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteUserAction
{
    public function __construct(
        private readonly DeleteUser $deleteUser,
        private readonly LegacySession $session,
        private readonly Translator $translator,
        private readonly UrlHelper $url,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        if ($this->deleteUser->execute((int) $args['id'], $this->session->userId())) {
            $this->session->flash($this->translator->_('User was deleted!'));
        } else {
            $this->session->flash($this->translator->_('Could not delete user!'));
        }

        return $response->withHeader('Location', $this->url->to('users'))->withStatus(302);
    }
}
