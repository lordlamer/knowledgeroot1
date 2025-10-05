<?php

/**
 * Show Content Use Case Response
 *
 * @package Knowledgeroot\Application\UseCase\Content
 */

declare(strict_types=1);

namespace Knowledgeroot\Application\UseCase\Content;

use Knowledgeroot\Domain\Content\Entity\Content;
use Knowledgeroot\Domain\User\Entity\User;

/**
 * Response DTO for ShowContentUseCase
 */
class ShowContentResponse
{
    /**
     * @param Content $content
     * @param User|null $author
     * @param array $categoryPath Array of Category entities
     * @param array $relatedContent Array of Content entities
     */
    public function __construct(
        public readonly Content $content,
        public readonly ?User $author,
        public readonly array $categoryPath,
        public readonly array $relatedContent
    ) {}

    /**
     * Convert to array for template rendering
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content->toArray(),
            'author' => $this->author?->toArray(),
            'category_path' => array_map(fn($cat) => $cat->toArray(), $this->categoryPath),
            'related_content' => array_map(fn($c) => $c->toArray(), $this->relatedContent),
        ];
    }
}
