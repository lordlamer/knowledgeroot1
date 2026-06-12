<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Session;

use Knowledgeroot\Domain\User\User;
use Knowledgeroot\Infrastructure\Config\Config;

/**
 * Bridge to the session of the legacy application.
 *
 * The legacy code names its session md5(base_url) and stores the login
 * state directly in $_SESSION (see knowledgeroot_auth::login() and
 * knowledgeroot_header::check_loged_in_userrights()). New code goes
 * through this class so both worlds share one session; the field layout
 * has to stay until the last legacy consumer is migrated.
 */
class LegacySession
{
    public function __construct(private readonly Config $config)
    {
    }

    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_trans_sid', '0');
        session_name(md5((string) $this->config->base->base_url));
        session_start();
    }

    public function login(User $user): void
    {
        $this->start();

        // the legacy code runs addslashes() over all request input, so the
        // user name is stored in its addslashes()ed form - md5hash has to
        // match md5(user . password) (see check_loged_in_userrights)
        $name = addslashes($user->name);

        $_SESSION['userid'] = $user->id;
        $_SESSION['groupid'] = $user->defaultGroup;
        $_SESSION['user'] = $name;
        $_SESSION['password'] = $user->passwordHash;
        $_SESSION['md5hash'] = md5($name . $user->passwordHash);
        $_SESSION['admin'] = $user->admin ? '1' : '0';
        $_SESSION['rightedit'] = $user->rightEdit ? '1' : '0';
        $_SESSION['open'] = $user->treeCache === '' ? [] : (unserialize($user->treeCache, ['allowed_classes' => false]) ?: []);
        $_SESSION['theme'] = $user->theme;
        $_SESSION['language'] = $user->language;
    }

    public function logout(): void
    {
        $this->start();

        $_SESSION = [];
        $_SESSION['user'] = 'guest';
        $_SESSION['password'] = 'guest';
        $_SESSION['md5hash'] = '';
        $_SESSION['cid'] = '';
        $_SESSION['userid'] = '';
        $_SESSION['groupid'] = '';
        $_SESSION['admin'] = '0';

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }

        session_destroy();
    }

    public function isLoggedIn(): bool
    {
        $this->start();

        return isset($_SESSION['userid']) && (string) $_SESSION['userid'] !== '' && (string) $_SESSION['userid'] !== '0';
    }

    public function isAdmin(): bool
    {
        $this->start();

        return $this->isLoggedIn() && isset($_SESSION['admin']) && (string) $_SESSION['admin'] === '1';
    }

    public function userId(): int
    {
        $this->start();

        return (int) ($_SESSION['userid'] ?? 0);
    }

    public function theme(): string
    {
        $this->start();

        return (string) ($_SESSION['theme'] ?? '');
    }

    public function language(): string
    {
        $this->start();

        return (string) ($_SESSION['language'] ?? '');
    }

    public function setPreferences(string $theme, string $language): void
    {
        $this->start();

        $_SESSION['theme'] = $theme;
        $_SESSION['language'] = $language;
    }

    /**
     * queue a message for the next rendered page
     */
    public function flash(string $message): void
    {
        $this->start();

        $_SESSION['kr_flash'][] = $message;
    }

    /**
     * @return string[] queued messages, clears the queue
     */
    public function consumeFlashes(): array
    {
        $this->start();

        $messages = $_SESSION['kr_flash'] ?? [];
        unset($_SESSION['kr_flash']);

        return $messages;
    }
}
