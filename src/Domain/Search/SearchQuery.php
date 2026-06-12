<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Search;

class SearchQuery
{
    /**
     * @param string[] $and words that all have to match
     * @param string[][] $orGroups groups of words where one per group has to match
     * @param string[] $not words that must not match
     */
    public function __construct(
        public readonly array $and,
        public readonly array $orGroups,
        public readonly array $not,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->and === [] && $this->orGroups === [] && $this->not === [];
    }
}
