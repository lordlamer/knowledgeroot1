<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Search;

interface SearchRepository
{
    /**
     * page id of a content element, used for the #<id> jump syntax
     */
    public function findContentPageId(int $contentId): ?int;

    /**
     * @param int[] $groupIds
     * @return array<int, array{id: int, pageId: int, title: string}>
     */
    public function searchContent(SearchQuery $query, int $userId, int $defaultGroupId, array $groupIds, bool $isAdmin): array;

    /**
     * @return array<int, array{id: int, title: string}>
     */
    public function searchPages(SearchQuery $query): array;

    /**
     * @return array<int, array{id: int, pageId: int, filename: string, filesize: int, date: string}>
     */
    public function searchFiles(SearchQuery $query): array;
}
