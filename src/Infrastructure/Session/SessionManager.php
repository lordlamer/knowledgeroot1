<?php

/**
 * Session Manager
 *
 * @package Knowledgeroot\Infrastructure\Session
 */

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Session;

use Knowledgeroot\Domain\User\Entity\User;

/**
 * Manages user sessions
 */
class SessionManager
{
    private bool $started = false;

    public function __construct()
    {
        $this->ensureStarted();
    }

    /**
     * Ensure session is started
     */
    private function ensureStarted(): void
    {
        if (!$this->started && session_status() === PHP_SESSION_NONE) {
            session_start();
            $this->started = true;
        }
    }

    /**
     * Set a session value
     */
    public function set(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if a session key exists
     */
    public function has(string $key): bool
    {
        $this->ensureStarted();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session value
     */
    public function remove(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    /**
     * Clear all session data
     */
    public function clear(): void
    {
        $this->ensureStarted();
        $_SESSION = [];
    }

    /**
     * Destroy the session completely
     */
    public function destroy(): void
    {
        $this->ensureStarted();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        $this->started = false;
    }

    /**
     * Regenerate session ID (for security after login)
     */
    public function regenerate(): void
    {
        $this->ensureStarted();
        session_regenerate_id(true);
    }

    /**
     * Login a user (store user data in session)
     */
    public function login(User $user, bool $remember = false): void
    {
        $this->regenerate();

        $this->set('user_id', $user->getId());
        $this->set('username', $user->getUsername());
        $this->set('login_time', time());

        if ($remember) {
            $this->set('remember', true);
        }
    }

    /**
     * Logout the current user
     */
    public function logout(): void
    {
        $this->clear();
        $this->destroy();
    }

    /**
     * Check if a user is logged in
     */
    public function isLoggedIn(): bool
    {
        return $this->has('user_id') && $this->has('username');
    }

    /**
     * Get the current user ID
     */
    public function getUserId(): ?int
    {
        return $this->get('user_id');
    }

    /**
     * Get the current username
     */
    public function getUsername(): ?string
    {
        return $this->get('username');
    }

    /**
     * Set a flash message (message that persists only for the next request)
     */
    public function setFlash(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Get and remove a flash message
     */
    public function getFlash(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Check if a flash message exists
     */
    public function hasFlash(string $key): bool
    {
        $this->ensureStarted();
        return isset($_SESSION['_flash'][$key]);
    }
}
