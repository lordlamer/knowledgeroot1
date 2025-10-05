<?php

/**
 * Show Content Use Case
 *
 * @package Knowledgeroot\Application\UseCase\Content
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\UseCase\Content;

use Knowledgeroot\Domain\Content\Service\ContentService;
use Knowledgeroot\Domain\User\Service\UserService;
use Knowledgeroot\Domain\Category\Service\CategoryService;

/**
 * Use Case: Display single content item with full context
 */
class ShowContentUseCase
{
    public function __construct(
        private ContentService $contentService,
        private UserService $userService,
        private CategoryService $categoryService
    ) {}

    public function execute(int $contentId): ShowContentResponse
    {
        $content = $this->contentService->getContentById($contentId);

        if (!$content) {
            throw new \DomainException("Content not found: {$contentId}");
        }

        // Get author information
        $author = $this->userService->getUserById($content->getUserId());

        // Get category breadcrumb
        $categoryPath = $this->categoryService->getCategoryPath($content->getCategoryId());

        // Get related content in same category
        $relatedContent = $this->contentService->getContentsByCategory($content->getCategoryId());
        // Remove current content from related
        $relatedContent = array_filter($relatedContent, fn($c) => $c->getId() !== $contentId);

        return new ShowContentResponse(
            content: $content,
            author: $author,
            categoryPath: $categoryPath,
            relatedContent: array_values($relatedContent)
        );
    }
}
