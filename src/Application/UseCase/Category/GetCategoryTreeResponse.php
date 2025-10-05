<?php

/**
 * Get Category Tree Response DTO
 *
 * @package Knowledgeroot\Application\UseCase\Category
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\UseCase\Category;

/**
 * Response object for GetCategoryTreeUseCase
 */
class GetCategoryTreeResponse
{
    public function __construct(
        private array $categoryTree,
        private ?int $currentCategoryId = null
    ) {}

    public function getCategoryTree(): array
    {
        return $this->categoryTree;
    }

    public function getCurrentCategoryId(): ?int
    {
        return $this->currentCategoryId;
    }

    /**
     * Convert to array for template rendering
     */
    public function toArray(): array
    {
        return [
            'category_tree' => $this->buildTreeArray($this->categoryTree),
            'current_category_id' => $this->currentCategoryId
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
