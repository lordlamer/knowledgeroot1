<?php

/**
 * List Content by Category Use Case
 *
 * @package Knowledgeroot\Application\UseCase\Content
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\UseCase\Content;

use Knowledgeroot\Domain\Content\Service\ContentService;
use Knowledgeroot\Domain\Category\Service\CategoryService;

/**
 * Use Case: List all content items in a category with category context
 */
class ListContentByCategoryUseCase
{
    public function __construct(
        private ContentService $contentService,
        private CategoryService $categoryService
    ) {}

    public function execute(int $categoryId): ListContentByCategoryResponse
    {
        $category = $this->categoryService->getCategoryById($categoryId);

        if (!$category) {
            throw new \DomainException("Category not found: {$categoryId}");
        }

        // Get all content in this category
        $contents = $this->contentService->getContentsByCategory($categoryId);

        // Get breadcrumb path
        $categoryPath = $this->categoryService->getCategoryPath($categoryId);

        // Get child categories
        $childCategories = $this->categoryService->getChildCategories($categoryId);

        return new ListContentByCategoryResponse(
            category: $category,
            contents: $contents,
            categoryPath: $categoryPath,
            childCategories: $childCategories
        );
    }
}
