<?php

/**
 * Category Entity
 *
 * @package Knowledgeroot\Domain\Category
 */

declare(strict_types=1);

namespace Knowledgeroot\Domain\Category\Entity;

use DateTimeImmutable;

/**
 * Category Domain Entity
 *
 * Represents a category/folder in the hierarchical tree structure
 */
class Category
{
    public function __construct(
        private ?int $id,
        private ?int $parentId,
        private int $userId,
        private string $name,
        private string $description,
        private string $icon,
        private bool $active,
        private int $sorting,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $changedAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getChangedAt(): DateTimeImmutable
    {
        return $this->changedAt;
    }

    /**
     * Check if this is a root category
     */
    public function isRoot(): bool
    {
        return $this->parentId === null || $this->parentId === 0;
    }

    /**
     * Update category details
     */
    public function updateDetails(string $name, string $description = '', string $icon = ''): void
    {
        $this->name = $name;
        if ($description !== '') {
            $this->description = $description;
        }
        if ($icon !== '') {
            $this->icon = $icon;
        }
        $this->changedAt = new DateTimeImmutable();
    }

    /**
     * Move to different parent category
     */
    public function moveTo(?int $newParentId): void
    {
        $this->parentId = $newParentId;
        $this->changedAt = new DateTimeImmutable();
    }

    /**
     * Update sorting order
     */
    public function updateSorting(int $sorting): void
    {
        $this->sorting = $sorting;
        $this->changedAt = new DateTimeImmutable();
    }

    /**
     * Activate category
     */
    public function activate(): void
    {
        $this->active = true;
        $this->changedAt = new DateTimeImmutable();
    }

    /**
     * Deactivate category
     */
    public function deactivate(): void
    {
        $this->active = false;
        $this->changedAt = new DateTimeImmutable();
    }

    /**
     * Convert to array for API responses
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parentId,
            'user_id' => $this->userId,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'active' => $this->active,
            'sorting' => $this->sorting,
            'is_root' => $this->isRoot(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'changed_at' => $this->changedAt->format('Y-m-d H:i:s'),
        ];
    }
}
