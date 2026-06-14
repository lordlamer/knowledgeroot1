<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Admin;

use Knowledgeroot\Infrastructure\Admin\AdminGate;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class AdminLoginAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly AdminGate $gate,
        private readonly Translator $translator,
        private readonly UrlHelper $url,
    ) {
    }

    public function show(Request $request, Response $response): Response
    {
        if ($this->gate->isAuthenticated()) {
            return $response->withHeader('Location', $this->url->to('admin'))->withStatus(302);
        }

        return $this->render($response, null, '');
    }

    public function submit(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();
        $user = (string) ($body['user'] ?? '');
        $pass = (string) ($body['pass'] ?? '');

        if ($this->gate->attempt($user, $pass)) {
            return $response->withHeader('Location', $this->url->to('admin'))->withStatus(302);
        }

        // mirror the legacy hint: show the hash for the entered credentials
        // so a locked-out admin can copy it into app.ini
        return $this->render(
            $response->withStatus(401),
            $this->translator->_('Wrong login!'),
            $this->gate->currentHashHint($user, $pass)
        );
    }

    private function render(Response $response, ?string $error, string $hashHint): Response
    {
        $response->getBody()->write($this->twig->render('admin/login.html', [
            'error' => $error,
            'hash_hint' => $hashHint,
        ]));

        return $response;
    }
}
