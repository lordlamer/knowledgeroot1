<?php

/**
 * Content Service
 *
 * @package Knowledgeroot\Domain\Content\Service
 */

declare(strict_types=1);

namespace Knowledgeroot\Domain\Content\Service;

use Knowledgeroot\Domain\Content\Entity\Content;
use Knowledgeroot\Domain\Content\Repository\ContentRepositoryInterface;
use DateTimeImmutable;

/**
 * Content Domain Service
 *
 * Contains business logic for content operations
 */
class ContentService
{
    public function __construct(
        private ContentRepositoryInterface $contentRepository
    ) {}

    public function getContentById(int $id): ?Content
    {
        return $this->contentRepository->findById($id);
    }

    public function getContentsByCategory(int $categoryId): array
    {
        return $this->contentRepository->findByCategory($categoryId);
    }

    public function getAllContent(): array
    {
        return $this->contentRepository->findAll();
    }

    public function searchContent(string $keyword): array
    {
        if (strlen($keyword) < 3) {
            return [];
        }

        return $this->contentRepository->search($keyword);
    }

    public function createContent(
        int $categoryId,
        int $userId,
        string $name,
        string $description,
        string $content,
        int $sorting = 0
    ): Content {
        $now = new DateTimeImmutable();

        $contentEntity = new Content(
            id: null,
            categoryId: $categoryId,
            userId: $userId,
            name: $name,
            description: $description,
            content: $content,
            active: true,
            sorting: $sorting,
            createdAt: $now,
            changedAt: $now
        );

        $this->contentRepository->save($contentEntity);

        return $contentEntity;
    }

    public function updateContent(
        int $id,
        string $name,
        string $content,
        string $description = ''
    ): void {
        $contentEntity = $this->contentRepository->findById($id);

        if (!$contentEntity) {
            throw new \DomainException("Content not found: {$id}");
        }

        $contentEntity->updateContent($name, $content, $description);
        $this->contentRepository->save($contentEntity);
    }

    public function deleteContent(int $id): void
    {
        $content = $this->contentRepository->findById($id);

        if (!$content) {
            throw new \DomainException("Content not found: {$id}");
        }

        $this->contentRepository->delete($content);
    }

    public function activateContent(int $id): void
    {
        $content = $this->contentRepository->findById($id);

        if (!$content) {
            throw new \DomainException("Content not found: {$id}");
        }

        $content->activate();
        $this->contentRepository->save($content);
    }

    public function deactivateContent(int $id): void
    {
        $content = $this->contentRepository->findById($id);

        if (!$content) {
            throw new \DomainException("Content not found: {$id}");
        }

        $content->deactivate();
        $this->contentRepository->save($content);
    }
}
