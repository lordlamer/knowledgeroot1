<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Doubles;

use Knowledgeroot\Domain\User\LoginAttempt;
use Knowledgeroot\Domain\User\LoginThrottleRepository;

class InMemoryThrottleRepository implements LoginThrottleRepository
{
    /** @var array<int, LoginAttempt> */
    public array $attempts = [];

    public ?int $purgedBefore = null;

    public function findByUserId(int $userId): ?LoginAttempt
    {
        return $this->attempts[$userId] ?? null;
    }

    public function recordFailure(int $userId, int $timestamp): void
    {
        $trials = isset($this->attempts[$userId]) ? $this->attempts[$userId]->trials + 1 : 1;
        $this->attempts[$userId] = new LoginAttempt($userId, $trials, $timestamp);
    }

    public function reset(int $userId, int $timestamp): void
    {
        if (isset($this->attempts[$userId])) {
            $this->attempts[$userId] = new LoginAttempt($userId, 0, $timestamp);
        }
    }

    public function purgeOlderThan(int $timestamp): void
    {
        $this->purgedBefore = $timestamp;
    }
}
