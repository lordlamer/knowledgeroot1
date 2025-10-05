<?php

/**
 * Show Category Use Case
 *
 * @package Knowledgeroot\Application\UseCase\Category
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\UseCase\Category;

use Knowledgeroot\Domain\Category\Service\CategoryService;
use Knowledgeroot\Domain\Content\Service\ContentService;

/**
 * Use case for displaying a category with its contents and children
 */
class ShowCategoryUseCase
{
    public function __construct(
        private CategoryService $categoryService,
        private ContentService $contentService
    ) {}

    /**
     * Execute the use case
     *
     * @param int $categoryId
     * @return ShowCategoryResponse
     * @throws \DomainException if category not found
     */
    public function execute(int $categoryId): ShowCategoryResponse
    {
        $category = $this->categoryService->getCategoryById($categoryId);

        if (!$category) {
            throw new \DomainException("Category not found: {$categoryId}");
        }

        // Get category path for breadcrumbs
        $categoryPath = $this->categoryService->getCategoryPath($categoryId);

        // Get child categories
        $childCategories = $this->categoryService->getChildCategories($categoryId);

        // Get contents in this category
        $contents = $this->contentService->getContentsByCategory($categoryId);

        // Get category tree for sidebar navigation
        $categoryTree = $this->categoryService->getCategoryTree();

        return new ShowCategoryResponse(
            category: $category,
            categoryPath: $categoryPath,
            childCategories: $childCategories,
            contents: $contents,
            categoryTree: $categoryTree
        );
    }
}
