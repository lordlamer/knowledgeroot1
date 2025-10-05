<?php

/**
 * Show Dashboard Use Case
 *
 * @package Knowledgeroot\Application\UseCase\Home
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\UseCase\Home;

use Knowledgeroot\Domain\Content\Service\ContentService;
use Knowledgeroot\Domain\Category\Service\CategoryService;

/**
 * Use case for displaying the dashboard/homepage
 */
class ShowDashboardUseCase
{
    public function __construct(
        private ContentService $contentService,
        private CategoryService $categoryService
    ) {}

    /**
     * Execute the use case
     *
     * @return ShowDashboardResponse
     */
    public function execute(): ShowDashboardResponse
    {
        // Get category tree for navigation
        $categoryTree = $this->categoryService->getCategoryTree();

        // Get root categories (top-level)
        $rootCategories = $this->categoryService->getChildCategories(null);

        // Get recently changed content (limit 10)
        $recentContent = $this->contentService->getRecentlyChangedContent(10);

        return new ShowDashboardResponse(
            categoryTree: $categoryTree,
            rootCategories: $rootCategories,
            recentContent: $recentContent
        );
    }
}
