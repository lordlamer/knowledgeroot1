<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Login;

use Knowledgeroot\Application\Login\AuthenticateUser;
use Knowledgeroot\Application\Login\AuthenticationFailure;
use Knowledgeroot\Infrastructure\Config\Config;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class SubmitLoginAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly AuthenticateUser $authenticateUser,
        private readonly LegacySession $session,
        private readonly Translator $translator,
        private readonly Config $config,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();
        $name = trim((string) ($body['user'] ?? ''));
        $password = (string) ($body['password'] ?? '');

        $result = $this->authenticateUser->authenticate($name, $password);

        if ($result->isSuccess()) {
            $this->session->login($result->user);

            return $response->withHeader('Location', 'index.php')->withStatus(302);
        }

        $error = match ($result->failure) {
            AuthenticationFailure::RetryDelay => str_replace(
                '#RELOGINDELAY#',
                (string) $this->config->login->delay,
                $this->translator->_('You can login after #RELOGINDELAY# seconds!')
            ),
            AuthenticationFailure::Blocked => $this->translator->_('This account is disabled!'),
            default => $this->translator->_('You have entered the wrong user or password!'),
        };

        $response->getBody()->write($this->twig->render('login/index.html', [
            'user' => $name,
            'error' => $error,
        ]));

        return $response->withStatus(401);
    }
}
