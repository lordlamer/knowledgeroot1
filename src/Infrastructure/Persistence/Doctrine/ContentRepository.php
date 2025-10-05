<?php

/**
 * Content Repository Implementation using Doctrine DBAL
 *
 * @package Knowledgeroot\Infrastructure\Persistence\Doctrine
 */

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\Content\Entity\Content;
use Knowledgeroot\Domain\Content\Repository\ContentRepositoryInterface;
use DateTimeImmutable;

/**
 * Doctrine DBAL implementation of ContentRepository
 */
class ContentRepository implements ContentRepositoryInterface
{
    public function __construct(
        private Connection $connection
    ) {}

    public function findById(int $id): ?Content
    {
        $sql = 'SELECT * FROM content WHERE id = :id';
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery(['id' => $id]);
        $row = $result->fetchAssociative();

        if (!$row) {
            return null;
        }

        return $this->hydrateContent($row);
    }

    public function findByCategory(int $categoryId): array
    {
        $sql = 'SELECT * FROM content WHERE parent_id = :categoryId AND active = 1 ORDER BY sorting ASC, name ASC';
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery(['categoryId' => $categoryId]);

        $contents = [];
        while ($row = $result->fetchAssociative()) {
            $contents[] = $this->hydrateContent($row);
        }

        return $contents;
    }

    public function findAll(): array
    {
        $sql = 'SELECT * FROM content WHERE active = 1 ORDER BY sorting ASC, name ASC';
        $result = $this->connection->executeQuery($sql);

        $contents = [];
        while ($row = $result->fetchAssociative()) {
            $contents[] = $this->hydrateContent($row);
        }

        return $contents;
    }

    public function save(Content $content): void
    {
        $data = [
            'parent_id' => $content->getCategoryId(),
            'user_id' => $content->getUserId(),
            'name' => $content->getName(),
            'description' => $content->getDescription(),
            'content' => $content->getContent(),
            'active' => $content->isActive() ? 1 : 0,
            'sorting' => $content->getSorting(),
            'timechange' => $content->getChangedAt()->format('Y-m-d H:i:s'),
        ];

        if ($content->getId() === null) {
            // Insert new content
            $data['timecreate'] = $content->getCreatedAt()->format('Y-m-d H:i:s');
            $this->connection->insert('content', $data);
        } else {
            // Update existing content
            $this->connection->update('content', $data, ['id' => $content->getId()]);
        }
    }

    public function delete(Content $content): void
    {
        if ($content->getId() !== null) {
            $this->connection->delete('content', ['id' => $content->getId()]);
        }
    }

    public function search(string $keyword): array
    {
        $sql = 'SELECT * FROM content WHERE active = 1 AND (name LIKE :keyword OR description LIKE :keyword OR content LIKE :keyword) ORDER BY sorting ASC, name ASC';
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery(['keyword' => '%' . $keyword . '%']);

        $contents = [];
        while ($row = $result->fetchAssociative()) {
            $contents[] = $this->hydrateContent($row);
        }

        return $contents;
    }

    /**
     * Hydrate database row into Content entity
     */
    private function hydrateContent(array $row): Content
    {
        return new Content(
            id: (int) $row['id'],
            categoryId: (int) $row['parent_id'],
            userId: (int) $row['user_id'],
            name: $row['name'],
            description: $row['description'] ?? '',
            content: $row['content'],
            active: (bool) $row['active'],
            sorting: (int) ($row['sorting'] ?? 0),
            createdAt: new DateTimeImmutable($row['timecreate']),
            changedAt: new DateTimeImmutable($row['timechange']),
        );
    }
}
