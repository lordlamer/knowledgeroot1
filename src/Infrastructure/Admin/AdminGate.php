<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Admin;

use Knowledgeroot\Infrastructure\Session\LegacySession;

/**
 * Break-glass authentication for the admin backend. Independent of the
 * normal user login: a single shared secret is stored as md5(user.pass)
 * in the [admin] loginhash of app.ini (same scheme as the legacy
 * admin/index.php). An empty loginhash locks the backend completely.
 */
class AdminGate
{
    private const SESSION_KEY = 'kr_admin_auth';

    public function __construct(
        private readonly LegacySession $session,
        private readonly string $loginHash,
    ) {
    }

    public function attempt(string $user, string $pass): bool
    {
        if ($this->loginHash === '') {
            return false;
        }

        if (!hash_equals($this->loginHash, md5($user . $pass))) {
            return false;
        }

        $this->session->start();
        $_SESSION[self::SESSION_KEY] = $this->loginHash;

        return true;
    }

    public function isAuthenticated(): bool
    {
        if ($this->loginHash === '') {
            return false;
        }

        $this->session->start();

        return ($_SESSION[self::SESSION_KEY] ?? '') === $this->loginHash;
    }

    public function logout(): void
    {
        $this->session->start();
        unset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * the current admin loginhash, shown on the login form so an admin
     * who just authenticated can copy it into app.ini (legacy behaviour)
     */
    public function currentHashHint(string $user, string $pass): string
    {
        return ($user !== '' && $pass !== '') ? md5($user . $pass) : '';
    }
}
