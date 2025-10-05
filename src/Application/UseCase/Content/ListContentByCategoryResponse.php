<?php

/**
 * List Content by Category Use Case Response
 *
 * @package Knowledgeroot\Application\UseCase\Content
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\UseCase\Content;

use Knowledgeroot\Domain\Category\Entity\Category;

/**
 * Response DTO for ListContentByCategoryUseCase
 */
class ListContentByCategoryResponse
{
    /**
     * @param Category $category
     * @param array $contents Array of Content entities
     * @param array $categoryPath Array of Category entities (breadcrumb)
     * @param array $childCategories Array of Category entities (subfolders)
     */
    public function __construct(
        public readonly Category $category,
        public readonly array $contents,
        public readonly array $categoryPath,
        public readonly array $childCategories
    ) {}

    public function toArray(): array
    {
        return [
            'category' => $this->category->toArray(),
            'contents' => array_map(fn($c) => $c->toArray(), $this->contents),
            'category_path' => array_map(fn($cat) => $cat->toArray(), $this->categoryPath),
            'child_categories' => array_map(fn($cat) => $cat->toArray(), $this->childCategories),
        ];
    }
}
