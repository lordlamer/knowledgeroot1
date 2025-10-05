<?php

/**
 * User Repository Interface
 *
 * @package Knowledgeroot\Domain\User\Repository
 */

declare(strict_types=1);

namespace Knowledgeroot\Domain\User\Repository;

use Knowledgeroot\Domain\User\Entity\User;

/**
 * Interface for User Repository
 */
interface UserRepositoryInterface
{
    /**
     * Find user by ID
     */
    public function findById(int $id): ?User;

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?User;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find all active users
     *
     * @return User[]
     */
    public function findAllActive(): array;

    /**
     * Find all users
     *
     * @return User[]
     */
    public function findAll(): array;

    /**
     * Save user (insert or update)
     */
    public function save(User $user): void;

    /**
     * Delete user
     */
    public function delete(User $user): void;

    /**
     * Check if username exists
     */
    public function usernameExists(string $username, ?int $excludeUserId = null): bool;

    /**
     * Check if email exists
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool;
}
