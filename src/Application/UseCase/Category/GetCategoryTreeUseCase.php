<?php

/**
 * Get Category Tree Use Case
 *
 * @package Knowledgeroot\Application\UseCase\Category
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\UseCase\Category;

use Knowledgeroot\Domain\Category\Service\CategoryService;

/**
 * Use case for getting the complete category tree
 */
class GetCategoryTreeUseCase
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    /**
     * Execute the use case
     *
     * @param int|null $currentCategoryId Currently active category for highlighting
     * @return GetCategoryTreeResponse
     */
    public function execute(?int $currentCategoryId = null): GetCategoryTreeResponse
    {
        $categoryTree = $this->categoryService->getCategoryTree();

        return new GetCategoryTreeResponse(
            categoryTree: $categoryTree,
            currentCategoryId: $currentCategoryId
        );
    }
}
