<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Options;

use Knowledgeroot\Application\UserManagement\InvalidUserData;
use Knowledgeroot\Application\UserSettings\ChangeOwnPassword;
use Knowledgeroot\Application\UserSettings\UpdateOwnPreferences;
use Knowledgeroot\Infrastructure\Language\LanguageLocator;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Theme\ThemeLocator;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class SaveOptionsAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ChangeOwnPassword $changePassword,
        private readonly UpdateOwnPreferences $updatePreferences,
        private readonly LegacySession $session,
        private readonly Translator $translator,
        private readonly ThemeLocator $themes,
        private readonly LanguageLocator $languages,
        private readonly UrlHelper $url,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();
        $userId = $this->session->userId();

        $theme = (string) ($body['theme'] ?? '');
        $language = (string) ($body['language'] ?? '');
        $password = (string) ($body['password'] ?? '');
        $confirmation = (string) ($body['password1'] ?? '');

        try {
            if ($theme !== $this->session->theme() || $language !== $this->session->language()) {
                $this->updatePreferences->execute($userId, $theme, $language);
                $this->session->setPreferences($theme, $language);
                $this->session->flash($this->translator->_('Theme was changed.'));
            }

            if ($password !== '' || $confirmation !== '') {
                $this->changePassword->execute($userId, $password, $confirmation);
                $this->session->flash($this->translator->_('Password changed!'));
            }
        } catch (InvalidUserData $e) {
            $response->getBody()->write($this->twig->render('options/index.html', [
                'error' => $this->translator->_($e->getMessage()),
                'flashes' => [],
                'themes' => $this->themes->themeNames(),
                'locales' => $this->languages->localeNames(),
                'current_theme' => $theme,
                'current_language' => $language,
            ]));

            return $response->withStatus(422);
        }

        return $response->withHeader('Location', $this->url->to('options'))->withStatus(302);
    }
}
