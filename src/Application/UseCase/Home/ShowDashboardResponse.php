<?php

/**
 * Show Dashboard Response DTO
 *
 * @package Knowledgeroot\Application\UseCase\Home
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\UseCase\Home;

/**
 * Response object for ShowDashboardUseCase
 */
class ShowDashboardResponse
{
    public function __construct(
        private array $categoryTree,
        private array $rootCategories,
        private array $recentContent
    ) {}

    public function getCategoryTree(): array
    {
        return $this->categoryTree;
    }

    public function getRootCategories(): array
    {
        return $this->rootCategories;
    }

    public function getRecentContent(): array
    {
        return $this->recentContent;
    }

    /**
     * Convert to array for template rendering
     */
    public function toArray(): array
    {
        return [
            'category_tree' => $this->buildTreeArray($this->categoryTree),
            'root_categories' => array_map(fn($cat) => $cat->toArray(), $this->rootCategories),
            'recent_content' => array_map(fn($content) => $content->toArray(), $this->recentContent)
        ];
    }

    /**
     * Build tree array for template
     */
    private function buildTreeArray(array $nodes): array
    {
        return array_map(function($node) {
            $result = $node['category']->toArray();
            if (!empty($node['children'])) {
                $result['children'] = $this->buildTreeArray($node['children']);
            }
            return $result;
        }, $nodes);
    }
}
