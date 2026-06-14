<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\User\LoginAttempt;
use Knowledgeroot\Domain\User\LoginThrottleRepository;

class DbalLoginThrottleRepository implements LoginThrottleRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function findByUserId(int $userId): ?LoginAttempt
    {
        $row = $this->connection->fetchAssociative(
            'SELECT usersid, login_trial, lasttrydate FROM users_login WHERE usersid = ?',
            [$userId]
        );

        if ($row === false) {
            return null;
        }

        return new LoginAttempt(
            userId: (int) $row['usersid'],
            trials: (int) $row['login_trial'],
            lastTryDate: (int) $row['lasttrydate'],
        );
    }

    public function recordFailure(int $userId, int $timestamp): void
    {
        $updated = $this->connection->executeStatement(
            'UPDATE users_login SET login_trial = login_trial + 1, lasttrydate = ? WHERE usersid = ?',
            [$timestamp, $userId]
        );

        if ($updated === 0) {
            $this->connection->executeStatement(
                'INSERT INTO users_login (usersid, login_trial, lasttrydate) VALUES (?, 1, ?)',
                [$userId, $timestamp]
            );
        }
    }

    public function reset(int $userId, int $timestamp): void
    {
        $this->connection->executeStatement(
            'UPDATE users_login SET login_trial = 0, lasttrydate = ? WHERE usersid = ?',
            [$timestamp, $userId]
        );
    }

    public function purgeOlderThan(int $timestamp): void
    {
        $this->connection->executeStatement(
            'DELETE FROM users_login WHERE lasttrydate < ?',
            [$timestamp]
        );
    }
}
