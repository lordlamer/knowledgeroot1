<?php

/**
 * Show Category Response DTO
 *
 * @package Knowledgeroot\Application\UseCase\Category
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\UseCase\Category;

use Knowledgeroot\Domain\Category\Entity\Category;

/**
 * Response object for ShowCategoryUseCase
 */
class ShowCategoryResponse
{
    public function __construct(
        private Category $category,
        private array $categoryPath,
        private array $childCategories,
        private array $contents,
        private array $categoryTree
    ) {}

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getCategoryPath(): array
    {
        return $this->categoryPath;
    }

    public function getChildCategories(): array
    {
        return $this->childCategories;
    }

    public function getContents(): array
    {
        return $this->contents;
    }

    public function getCategoryTree(): array
    {
        return $this->categoryTree;
    }

    /**
     * Convert to array for template rendering
     */
    public function toArray(): array
    {
        return [
            'category' => $this->category->toArray(),
            'category_path' => array_map(fn($cat) => $cat->toArray(), $this->categoryPath),
            'child_categories' => array_map(fn($cat) => $cat->toArray(), $this->childCategories),
            'contents' => array_map(fn($content) => $content->toArray(), $this->contents),
            'category_tree' => $this->buildTreeArray($this->categoryTree),
            'current_category_id' => $this->category->getId()
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
