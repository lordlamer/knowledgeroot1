<?php

/**
 * Content Repository Interface
 *
 * @package Knowledgeroot\Domain\Content\Repository
 */

declare(strict_types=1);

namespace Knowledgeroot\Domain\Content\Repository;

use Knowledgeroot\Domain\Content\Entity\Content;

/**
 * Interface for Content Repository
 */
interface ContentRepositoryInterface
{
    /**
     * Find content by ID
     */
    public function findById(int $id): ?Content;

    /**
     * Find all content items in a category
     *
     * @return Content[]
     */
    public function findByCategory(int $categoryId): array;

    /**
     * Find all active content
     *
     * @return Content[]
     */
    public function findAll(): array;

    /**
     * Save content (insert or update)
     */
    public function save(Content $content): void;

    /**
     * Delete content
     */
    public function delete(Content $content): void;

    /**
     * Search content by keyword
     *
     * @return Content[]
     */
    public function search(string $keyword): array;
}
