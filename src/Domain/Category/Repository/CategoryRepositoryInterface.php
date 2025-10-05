<?php

/**
 * Category Repository Interface
 *
 * @package Knowledgeroot\Domain\Category\Repository
 */

declare(strict_types=1);

namespace Knowledgeroot\Domain\Category\Repository;

use Knowledgeroot\Domain\Category\Entity\Category;

/**
 * Interface for Category Repository
 */
interface CategoryRepositoryInterface
{
    /**
     * Find category by ID
     */
    public function findById(int $id): ?Category;

    /**
     * Find all child categories of a parent
     *
     * @return Category[]
     */
    public function findByParentId(?int $parentId): array;

    /**
     * Find all root categories
     *
     * @return Category[]
     */
    public function findRootCategories(): array;

    /**
     * Find all active categories
     *
     * @return Category[]
     */
    public function findAll(): array;

    /**
     * Get full category path from root to category
     *
     * @return Category[]
     */
    public function getCategoryPath(int $categoryId): array;

    /**
     * Get all descendant categories (recursive)
     *
     * @return Category[]
     */
    public function getDescendants(int $categoryId): array;

    /**
     * Save category (insert or update)
     */
    public function save(Category $category): void;

    /**
     * Delete category
     */
    public function delete(Category $category): void;

    /**
     * Check if category has children
     */
    public function hasChildren(int $categoryId): bool;

    /**
     * Get category tree as nested array
     */
    public function getTree(?int $parentId = null): array;
}
