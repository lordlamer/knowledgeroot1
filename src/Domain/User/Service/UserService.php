<?php

/**
 * User Service
 *
 * @package Knowledgeroot\Domain\User\Service
 */

declare(strict_types=1);

namespace Knowledgeroot\Domain\User\Service;

use Knowledgeroot\Domain\User\Entity\User;
use Knowledgeroot\Domain\User\Repository\UserRepositoryInterface;
use DateTimeImmutable;

/**
 * User Domain Service
 *
 * Contains business logic for user operations
 */
class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function getUserByUsername(string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }

    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    public function getAllActiveUsers(): array
    {
        return $this->userRepository->findAllActive();
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    /**
     * Authenticate user with username/password
     */
    public function authenticate(string $username, string $password): ?User
    {
        $user = $this->userRepository->findByUsername($username);

        if (!$user) {
            return null;
        }

        if (!$user->isActive()) {
            return null;
        }

        if (!$user->verifyPassword($password)) {
            return null;
        }

        // Record successful login
        $user->recordLogin();
        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Create new user
     */
    public function createUser(
        string $username,
        string $email,
        string $password,
        string $firstName = '',
        string $lastName = '',
        string $language = 'en_US'
    ): User {
        // Validate username uniqueness
        if ($this->userRepository->usernameExists($username)) {
            throw new \DomainException("Username already exists: {$username}");
        }

        // Validate email uniqueness
        if ($this->userRepository->emailExists($email)) {
            throw new \DomainException("Email already exists: {$email}");
        }

        // Validate password strength
        if (strlen($password) < 8) {
            throw new \DomainException("Password must be at least 8 characters long");
        }

        $now = new DateTimeImmutable();
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $user = new User(
            id: null,
            username: $username,
            email: $email,
            passwordHash: $passwordHash,
            firstName: $firstName,
            lastName: $lastName,
            active: true,
            language: $language,
            createdAt: $now,
            changedAt: $now
        );

        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Update user profile
     */
    public function updateUserProfile(
        int $userId,
        string $firstName,
        string $lastName,
        string $email,
        string $language = ''
    ): void {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new \DomainException("User not found: {$userId}");
        }

        // Validate email uniqueness (excluding current user)
        if ($email !== $user->getEmail() && $this->userRepository->emailExists($email, $userId)) {
            throw new \DomainException("Email already exists: {$email}");
        }

        $user->updateProfile($firstName, $lastName, $email, $language);
        $this->userRepository->save($user);
    }

    /**
     * Change user password
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): void
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new \DomainException("User not found: {$userId}");
        }

        if (!$user->verifyPassword($oldPassword)) {
            throw new \DomainException("Current password is incorrect");
        }

        if (strlen($newPassword) < 8) {
            throw new \DomainException("New password must be at least 8 characters long");
        }

        $user->updatePassword($newPassword);
        $this->userRepository->save($user);
    }

    /**
     * Activate user account
     */
    public function activateUser(int $userId): void
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new \DomainException("User not found: {$userId}");
        }

        $user->activate();
        $this->userRepository->save($user);
    }

    /**
     * Deactivate user account
     */
    public function deactivateUser(int $userId): void
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new \DomainException("User not found: {$userId}");
        }

        $user->deactivate();
        $this->userRepository->save($user);
    }

    /**
     * Delete user
     */
    public function deleteUser(int $userId): void
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new \DomainException("User not found: {$userId}");
        }

        $this->userRepository->delete($user);
    }
}
