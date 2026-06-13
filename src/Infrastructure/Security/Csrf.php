<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Security;

use Knowledgeroot\Infrastructure\Session\LegacySession;

/**
 * Per-session CSRF token. Generated lazily on first read (when a form is
 * rendered) and validated on state-changing requests.
 */
class Csrf
{
    public const FIELD = '_csrf';

    private const SESSION_KEY = 'kr_csrf';

    public function __construct(private readonly LegacySession $session)
    {
    }

    public function token(): string
    {
        $this->session->start();

        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public function isValid(?string $token): bool
    {
        $this->session->start();

        $stored = $_SESSION[self::SESSION_KEY] ?? '';

        return is_string($token) && $token !== '' && $stored !== '' && hash_equals($stored, $token);
    }
}
