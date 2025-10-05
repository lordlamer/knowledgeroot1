<?php

/**
 * User Entity
 *
 * @package Knowledgeroot\Domain\User
 */

declare(strict_types=1);

namespace Knowledgeroot\Domain\User\Entity;

use DateTimeImmutable;

/**
 * User Domain Entity
 *
 * Represents a user in the system with authentication and profile data
 */
class User
{
    public function __construct(
        private ?int $id,
        private string $username,
        private string $email,
        private string $passwordHash,
        private string $firstName,
        private string $lastName,
        private bool $active,
        private string $language,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $changedAt,
        private ?DateTimeImmutable $lastLogin = null,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getChangedAt(): DateTimeImmutable
    {
        return $this->changedAt;
    }

    public function getLastLogin(): ?DateTimeImmutable
    {
        return $this->lastLogin;
    }

    /**
     * Verify password against hash
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    /**
     * Update password with new hash
     */
    public function updatePassword(string $newPassword): void
    {
        $this->passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->changedAt = new DateTimeImmutable();
    }

    /**
     * Update profile information
     */
    public function updateProfile(
        string $firstName,
        string $lastName,
        string $email,
        string $language = ''
    ): void {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        if ($language !== '') {
            $this->language = $language;
        }
        $this->changedAt = new DateTimeImmutable();
    }

    /**
     * Activate user account
     */
    public function activate(): void
    {
        $this->active = true;
        $this->changedAt = new DateTimeImmutable();
    }

    /**
     * Deactivate user account
     */
    public function deactivate(): void
    {
        $this->active = false;
        $this->changedAt = new DateTimeImmutable();
    }

    /**
     * Record successful login
     */
    public function recordLogin(): void
    {
        $this->lastLogin = new DateTimeImmutable();
    }

    /**
     * Convert to array (without password hash)
     */
    public function toArray(bool $includePassword = false): array
    {
        $data = [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'full_name' => $this->getFullName(),
            'active' => $this->active,
            'language' => $this->language,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'changed_at' => $this->changedAt->format('Y-m-d H:i:s'),
            'last_login' => $this->lastLogin?->format('Y-m-d H:i:s'),
        ];

        if ($includePassword) {
            $data['password_hash'] = $this->passwordHash;
        }

        return $data;
    }
}
