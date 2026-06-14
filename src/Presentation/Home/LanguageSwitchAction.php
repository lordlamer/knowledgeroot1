<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Home;

use Knowledgeroot\Infrastructure\Language\LanguageLocator;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Switches the interface language for the current session. Available to
 * everyone (guests included), like the legacy front-page dropdown.
 */
class LanguageSwitchAction
{
    public function __construct(
        private readonly LegacySession $session,
        private readonly LanguageLocator $languages,
        private readonly UrlHelper $url,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $language = (string) (((array) $request->getParsedBody())['language'] ?? '');

        if (in_array($language, $this->languages->localeNames(), true)) {
            $this->session->setLanguage($language);
        }

        // return where the user came from, falling back to the front page
        $referer = $request->getHeaderLine('Referer');
        $target = $referer !== '' ? $referer : $this->url->to('');

        return $response->withHeader('Location', $target)->withStatus(302);
    }
}
