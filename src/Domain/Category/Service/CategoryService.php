<?php

/**
 * Category Service
 *
 * @package Knowledgeroot\Domain\Category\Service
 */

declare(strict_types=1);

namespace Knowledgeroot\Domain\Category\Service;

use Knowledgeroot\Domain\Category\Entity\Category;
use Knowledgeroot\Domain\Category\Repository\CategoryRepositoryInterface;
use DateTimeImmutable;

/**
 * Category Domain Service
 *
 * Contains business logic for category/tree operations
 */
class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->findById($id);
    }

    public function getChildCategories(?int $parentId): array
    {
        return $this->categoryRepository->findByParentId($parentId);
    }

    public function getRootCategories(): array
    {
        return $this->categoryRepository->findRootCategories();
    }

    public function getAllCategories(): array
    {
        return $this->categoryRepository->findAll();
    }

    public function getCategoryPath(int $categoryId): array
    {
        return $this->categoryRepository->getCategoryPath($categoryId);
    }

    public function getCategoryTree(?int $parentId = null): array
    {
        return $this->categoryRepository->getTree($parentId);
    }

    /**
     * Create new category
     */
    public function createCategory(
        ?int $parentId,
        int $userId,
        string $name,
        string $description = '',
        string $icon = '',
        int $sorting = 0
    ): Category {
        // Validate parent exists if specified
        if ($parentId !== null && $parentId > 0) {
            $parent = $this->categoryRepository->findById($parentId);
            if (!$parent) {
                throw new \DomainException("Parent category not found: {$parentId}");
            }
        }

        $now = new DateTimeImmutable();

        $category = new Category(
            id: null,
            parentId: $parentId,
            userId: $userId,
            name: $name,
            description: $description,
            icon: $icon,
            active: true,
            sorting: $sorting,
            createdAt: $now,
            changedAt: $now
        );

        $this->categoryRepository->save($category);

        return $category;
    }

    /**
     * Update category details
     */
    public function updateCategory(
        int $id,
        string $name,
        string $description = '',
        string $icon = ''
    ): void {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new \DomainException("Category not found: {$id}");
        }

        $category->updateDetails($name, $description, $icon);
        $this->categoryRepository->save($category);
    }

    /**
     * Move category to different parent
     */
    public function moveCategory(int $id, ?int $newParentId): void
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new \DomainException("Category not found: {$id}");
        }

        // Prevent moving category into itself or its descendants
        if ($newParentId !== null && $newParentId > 0) {
            if ($newParentId === $id) {
                throw new \DomainException("Cannot move category into itself");
            }

            $descendants = $this->categoryRepository->getDescendants($id);
            foreach ($descendants as $descendant) {
                if ($descendant->getId() === $newParentId) {
                    throw new \DomainException("Cannot move category into its own descendant");
                }
            }

            // Validate new parent exists
            $newParent = $this->categoryRepository->findById($newParentId);
            if (!$newParent) {
                throw new \DomainException("New parent category not found: {$newParentId}");
            }
        }

        $category->moveTo($newParentId);
        $this->categoryRepository->save($category);
    }

    /**
     * Delete category
     */
    public function deleteCategory(int $id): void
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new \DomainException("Category not found: {$id}");
        }

        // Check if category has children
        if ($this->categoryRepository->hasChildren($id)) {
            throw new \DomainException("Cannot delete category with children. Move or delete children first.");
        }

        $this->categoryRepository->delete($category);
    }

    /**
     * Activate category
     */
    public function activateCategory(int $id): void
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new \DomainException("Category not found: {$id}");
        }

        $category->activate();
        $this->categoryRepository->save($category);
    }

    /**
     * Deactivate category
     */
    public function deactivateCategory(int $id): void
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new \DomainException("Category not found: {$id}");
        }

        $category->deactivate();
        $this->categoryRepository->save($category);
    }
}
