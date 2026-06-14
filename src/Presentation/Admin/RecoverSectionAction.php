<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Admin;

use Knowledgeroot\Application\Admin\CreateAdminUser;
use Knowledgeroot\Application\Admin\ResetUserPassword;
use Knowledgeroot\Application\UserManagement\InvalidUserData;
use Knowledgeroot\Domain\User\UserRepository;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

/**
 * Break-glass user recovery: list users, reset a password, create an
 * administrator.
 */
class RecoverSectionAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly UserRepository $users,
        private readonly ResetUserPassword $resetPassword,
        private readonly CreateAdminUser $createAdminUser,
        private readonly LegacySession $session,
        private readonly Translator $translator,
        private readonly UrlHelper $url,
    ) {
    }

    public function show(Request $request, Response $response): Response
    {
        $response->getBody()->write($this->twig->render('admin/recover.html', [
            'active' => 'recover',
            'users' => $this->users->findAll(),
            'flashes' => $this->session->consumeFlashes(),
        ]));

        return $response;
    }

    public function reset(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();

        try {
            $this->resetPassword->execute((int) ($body['userid'] ?? 0), (string) ($body['password'] ?? ''));
            $this->session->flash($this->translator->_('Password was set'));
        } catch (InvalidUserData $e) {
            $this->session->flash($this->translator->_($e->getMessage()));
        }

        return $response->withHeader('Location', $this->url->to('admin/recover'))->withStatus(302);
    }

    public function create(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();

        try {
            $this->createAdminUser->execute((string) ($body['username'] ?? ''), (string) ($body['password'] ?? ''));
            $this->session->flash($this->translator->_('Created user'));
        } catch (InvalidUserData $e) {
            $this->session->flash($this->translator->_($e->getMessage()));
        }

        return $response->withHeader('Location', $this->url->to('admin/recover'))->withStatus(302);
    }
}
