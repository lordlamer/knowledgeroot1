<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\User;

/**
 * Tracks failed login attempts per user (users_login table) so that
 * repeated failures are delayed and eventually blocked.
 */
interface LoginThrottleRepository
{
    public function findByUserId(int $userId): ?LoginAttempt;

    public function recordFailure(int $userId, int $timestamp): void;

    public function reset(int $userId, int $timestamp): void;

    public function purgeOlderThan(int $timestamp): void;
}
