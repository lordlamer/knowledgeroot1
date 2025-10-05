<?php

/**
 * User Repository Implementation using Doctrine DBAL
 *
 * @package Knowledgeroot\Infrastructure\Persistence\Doctrine
 */

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\User\Entity\User;
use Knowledgeroot\Domain\User\Repository\UserRepositoryInterface;
use DateTimeImmutable;

/**
 * Doctrine DBAL implementation of UserRepository
 */
class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private Connection $connection
    ) {}

    public function findById(int $id): ?User
    {
        $sql = 'SELECT * FROM user WHERE id = :id';
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery(['id' => $id]);
        $row = $result->fetchAssociative();

        if (!$row) {
            return null;
        }

        return $this->hydrateUser($row);
    }

    public function findByUsername(string $username): ?User
    {
        $sql = 'SELECT * FROM user WHERE username = :username';
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery(['username' => $username]);
        $row = $result->fetchAssociative();

        if (!$row) {
            return null;
        }

        return $this->hydrateUser($row);
    }

    public function findByEmail(string $email): ?User
    {
        $sql = 'SELECT * FROM user WHERE email = :email';
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery(['email' => $email]);
        $row = $result->fetchAssociative();

        if (!$row) {
            return null;
        }

        return $this->hydrateUser($row);
    }

    public function findAllActive(): array
    {
        $sql = 'SELECT * FROM user WHERE active = 1 ORDER BY username ASC';
        $result = $this->connection->executeQuery($sql);

        $users = [];
        while ($row = $result->fetchAssociative()) {
            $users[] = $this->hydrateUser($row);
        }

        return $users;
    }

    public function findAll(): array
    {
        $sql = 'SELECT * FROM user ORDER BY username ASC';
        $result = $this->connection->executeQuery($sql);

        $users = [];
        while ($row = $result->fetchAssociative()) {
            $users[] = $this->hydrateUser($row);
        }

        return $users;
    }

    public function save(User $user): void
    {
        $data = [
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'password' => $user->getPasswordHash(),
            'firstname' => $user->getFirstName(),
            'lastname' => $user->getLastName(),
            'active' => $user->isActive() ? 1 : 0,
            'language' => $user->getLanguage(),
            'timechange' => $user->getChangedAt()->format('Y-m-d H:i:s'),
        ];

        if ($user->getLastLogin()) {
            $data['lastlogin'] = $user->getLastLogin()->format('Y-m-d H:i:s');
        }

        if ($user->getId() === null) {
            // Insert new user
            $data['timecreate'] = $user->getCreatedAt()->format('Y-m-d H:i:s');
            $this->connection->insert('user', $data);
        } else {
            // Update existing user
            $this->connection->update('user', $data, ['id' => $user->getId()]);
        }
    }

    public function delete(User $user): void
    {
        if ($user->getId() !== null) {
            $this->connection->delete('user', ['id' => $user->getId()]);
        }
    }

    public function usernameExists(string $username, ?int $excludeUserId = null): bool
    {
        $sql = 'SELECT COUNT(*) as count FROM user WHERE username = :username';
        $params = ['username' => $username];

        if ($excludeUserId !== null) {
            $sql .= ' AND id != :excludeUserId';
            $params['excludeUserId'] = $excludeUserId;
        }

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($params);
        $row = $result->fetchAssociative();

        return ($row['count'] ?? 0) > 0;
    }

    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        $sql = 'SELECT COUNT(*) as count FROM user WHERE email = :email';
        $params = ['email' => $email];

        if ($excludeUserId !== null) {
            $sql .= ' AND id != :excludeUserId';
            $params['excludeUserId'] = $excludeUserId;
        }

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($params);
        $row = $result->fetchAssociative();

        return ($row['count'] ?? 0) > 0;
    }

    /**
     * Hydrate database row into User entity
     */
    private function hydrateUser(array $row): User
    {
        return new User(
            id: (int) $row['id'],
            username: $row['username'],
            email: $row['email'],
            passwordHash: $row['password'],
            firstName: $row['firstname'] ?? '',
            lastName: $row['lastname'] ?? '',
            active: (bool) $row['active'],
            language: $row['language'] ?? 'en_US',
            createdAt: new DateTimeImmutable($row['timecreate']),
            changedAt: new DateTimeImmutable($row['timechange']),
            lastLogin: isset($row['lastlogin']) ? new DateTimeImmutable($row['lastlogin']) : null,
        );
    }
}
