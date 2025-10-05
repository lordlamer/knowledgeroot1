<?php

/**
 * Category Repository Implementation using Doctrine DBAL
 *
 * @package Knowledgeroot\Infrastructure\Persistence\Doctrine
 */

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\Category\Entity\Category;
use Knowledgeroot\Domain\Category\Repository\CategoryRepositoryInterface;
use DateTimeImmutable;

/**
 * Doctrine DBAL implementation of CategoryRepository
 */
class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        private Connection $connection
    ) {}

    public function findById(int $id): ?Category
    {
        $sql = 'SELECT * FROM page WHERE id = :id';
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery(['id' => $id]);
        $row = $result->fetchAssociative();

        if (!$row) {
            return null;
        }

        return $this->hydrateCategory($row);
    }

    public function findByParentId(?int $parentId): array
    {
        $parentId = $parentId ?? 0;
        $sql = 'SELECT * FROM page WHERE parent_id = :parentId AND active = 1 ORDER BY sorting ASC, name ASC';
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery(['parentId' => $parentId]);

        $categories = [];
        while ($row = $result->fetchAssociative()) {
            $categories[] = $this->hydrateCategory($row);
        }

        return $categories;
    }

    public function findRootCategories(): array
    {
        return $this->findByParentId(0);
    }

    public function findAll(): array
    {
        $sql = 'SELECT * FROM page WHERE active = 1 ORDER BY parent_id ASC, sorting ASC, name ASC';
        $result = $this->connection->executeQuery($sql);

        $categories = [];
        while ($row = $result->fetchAssociative()) {
            $categories[] = $this->hydrateCategory($row);
        }

        return $categories;
    }

    public function getCategoryPath(int $categoryId): array
    {
        $path = [];
        $currentId = $categoryId;

        while ($currentId > 0) {
            $category = $this->findById($currentId);
            if (!$category) {
                break;
            }

            array_unshift($path, $category);
            $currentId = $category->getParentId() ?? 0;
        }

        return $path;
    }

    public function getDescendants(int $categoryId): array
    {
        $descendants = [];
        $children = $this->findByParentId($categoryId);

        foreach ($children as $child) {
            $descendants[] = $child;
            // Recursively get descendants
            $childDescendants = $this->getDescendants($child->getId());
            $descendants = array_merge($descendants, $childDescendants);
        }

        return $descendants;
    }

    public function save(Category $category): void
    {
        $data = [
            'parent_id' => $category->getParentId() ?? 0,
            'user_id' => $category->getUserId(),
            'name' => $category->getName(),
            'description' => $category->getDescription(),
            'icon' => $category->getIcon(),
            'active' => $category->isActive() ? 1 : 0,
            'sorting' => $category->getSorting(),
            'timechange' => $category->getChangedAt()->format('Y-m-d H:i:s'),
        ];

        if ($category->getId() === null) {
            // Insert new category
            $data['timecreate'] = $category->getCreatedAt()->format('Y-m-d H:i:s');
            $this->connection->insert('page', $data);
        } else {
            // Update existing category
            $this->connection->update('page', $data, ['id' => $category->getId()]);
        }
    }

    public function delete(Category $category): void
    {
        if ($category->getId() !== null) {
            $this->connection->delete('page', ['id' => $category->getId()]);
        }
    }

    public function hasChildren(int $categoryId): bool
    {
        $sql = 'SELECT COUNT(*) as count FROM page WHERE parent_id = :parentId';
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery(['parentId' => $categoryId]);
        $row = $result->fetchAssociative();

        return ($row['count'] ?? 0) > 0;
    }

    public function getTree(?int $parentId = null): array
    {
        $parentId = $parentId ?? 0;
        $categories = $this->findByParentId($parentId);

        $tree = [];
        foreach ($categories as $category) {
            $node = $category->toArray();
            $node['children'] = $this->getTree($category->getId());
            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * Hydrate database row into Category entity
     */
    private function hydrateCategory(array $row): Category
    {
        return new Category(
            id: (int) $row['id'],
            parentId: ($row['parent_id'] ?? 0) > 0 ? (int) $row['parent_id'] : null,
            userId: (int) $row['user_id'],
            name: $row['name'],
            description: $row['description'] ?? '',
            icon: $row['icon'] ?? '',
            active: (bool) $row['active'],
            sorting: (int) ($row['sorting'] ?? 0),
            createdAt: new DateTimeImmutable($row['timecreate']),
            changedAt: new DateTimeImmutable($row['timechange']),
        );
    }
}
