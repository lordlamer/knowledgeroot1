<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\Login;

use Knowledgeroot\Domain\User\LoginThrottleRepository;
use Knowledgeroot\Domain\User\UserRepository;

/**
 * Use case: authenticate a user by name and password.
 *
 * Mirrors the legacy behaviour: failed attempts are throttled per user
 * (configured delay between attempts, hard block after the configured
 * maximum) and recorded in the users_login table.
 */
class AuthenticateUser
{
    /**
     * entries older than this many seconds (plus the configured delay)
     * are purged from the throttle table after a successful login
     */
    private const GC_AGE_SECONDS = 6000;

    public function __construct(
        private readonly UserRepository $users,
        private readonly LoginThrottleRepository $throttle,
        private readonly int $retryDelaySeconds,
        private readonly int $maxAttempts,
    ) {
    }

    public function authenticate(string $name, string $password): AuthenticationResult
    {
        $user = $this->users->findEnabledByName($name);

        if ($user === null) {
            return AuthenticationResult::failure(AuthenticationFailure::InvalidCredentials);
        }

        $now = time();
        $attempt = $this->throttle->findByUserId($user->id);

        if ($attempt !== null && $attempt->trials > 0 && $attempt->lastTryDate + $this->retryDelaySeconds > $now) {
            return AuthenticationResult::failure(AuthenticationFailure::RetryDelay);
        }

        if ($attempt !== null && $attempt->trials >= $this->maxAttempts) {
            return AuthenticationResult::failure(AuthenticationFailure::Blocked);
        }

        if (!$this->passwordMatches($password, $user->passwordHash)) {
            $this->throttle->recordFailure($user->id, $now);

            return AuthenticationResult::failure(AuthenticationFailure::InvalidCredentials);
        }

        $this->throttle->reset($user->id, $now);
        $this->throttle->purgeOlderThan($now - (self::GC_AGE_SECONDS + $this->retryDelaySeconds));

        return AuthenticationResult::success($user);
    }

    private function passwordMatches(string $password, string $storedHash): bool
    {
        // modern hashes (password_hash) - the target format once user
        // management writes through this layer
        $hashInfo = password_get_info($storedHash);
        if ($hashInfo['algo'] !== null) {
            return password_verify($password, $storedHash);
        }

        // legacy format: md5 over the addslashes()ed password, see the
        // old knowledgeroot_auth::login()
        return hash_equals($storedHash, md5(addslashes($password)));
    }
}
