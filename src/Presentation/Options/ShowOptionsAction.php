<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Options;

use Knowledgeroot\Infrastructure\Language\LanguageLocator;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Theme\ThemeLocator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class ShowOptionsAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly LegacySession $session,
        private readonly ThemeLocator $themes,
        private readonly LanguageLocator $languages,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $response->getBody()->write($this->twig->render('options/index.html', [
            'error' => null,
            'flashes' => $this->session->consumeFlashes(),
            'themes' => $this->themes->themeNames(),
            'locales' => $this->languages->localeNames(),
            'current_theme' => $this->session->theme(),
            'current_language' => $this->session->language(),
        ]));

        return $response;
    }
}
