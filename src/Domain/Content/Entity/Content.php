<?php

/**
 * Content Entity
 *
 * @package Knowledgeroot\Domain\Content
 */

declare(strict_types=1);

namespace Knowledgeroot\Domain\Content\Entity;

use DateTimeImmutable;

/**
 * Content Domain Entity
 *
 * Represents a single piece of content in the knowledge base
 */
class Content
{
    public function __construct(
        private ?int $id,
        private int $categoryId,
        private int $userId,
        private string $name,
        private string $description,
        private string $content,
        private bool $active,
        private int $sorting,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $changedAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
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

    public function getContent(): string
    {
        return $this->content;
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
     * Update content name and body
     */
    public function updateContent(string $name, string $content, string $description = ''): void
    {
        $this->name = $name;
        $this->content = $content;
        if ($description !== '') {
            $this->description = $description;
        }
        $this->changedAt = new DateTimeImmutable();
    }

    /**
     * Activate content
     */
    public function activate(): void
    {
        $this->active = true;
        $this->changedAt = new DateTimeImmutable();
    }

    /**
     * Deactivate content
     */
    public function deactivate(): void
    {
        $this->active = false;
        $this->changedAt = new DateTimeImmutable();
    }

    /**
     * Move to different category
     */
    public function moveToCategory(int $categoryId): void
    {
        $this->categoryId = $categoryId;
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
     * Convert to array for API responses
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->categoryId,
            'user_id' => $this->userId,
            'name' => $this->name,
            'description' => $this->description,
            'content' => $this->content,
            'active' => $this->active,
            'sorting' => $this->sorting,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'changed_at' => $this->changedAt->format('Y-m-d H:i:s'),
        ];
    }
}
