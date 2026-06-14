<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\Search;

use Knowledgeroot\Domain\Page\PageAccess;
use Knowledgeroot\Domain\Page\PagePathResolver;
use Knowledgeroot\Domain\Search\SearchQueryParser;
use Knowledgeroot\Domain\Search\SearchRepository;
use Knowledgeroot\Domain\User\UserRepository;

/**
 * Use case: full text search over content, pages and file names.
 *
 * Results are filtered by the page permission model - a hit is only
 * returned when the user may read the page and all its ancestors.
 * Searching for #<id> jumps directly to the content element.
 */
class Search
{
    public function __construct(
        private readonly SearchQueryParser $parser,
        private readonly SearchRepository $repository,
        private readonly UserRepository $users,
        private readonly PageAccess $access,
        private readonly PagePathResolver $paths,
    ) {
    }

    public function execute(string $searchString, int $userId): SearchOutcome
    {
        $searchString = trim($searchString);

        // direct jump to a content element: #123
        if (preg_match('/#([0-9]+)/', $searchString, $match)) {
            $pageId = $this->repository->findContentPageId((int) $match[1]);

            if ($pageId !== null && $this->access->canRead($pageId, $userId)) {
                return SearchOutcome::jump($pageId, (int) $match[1]);
            }
        }

        $query = $this->parser->parse($searchString);
        if ($query->isEmpty()) {
            return SearchOutcome::results([], [], []);
        }

        $user = $userId > 0 ? $this->users->findById($userId) : null;
        $isAdmin = $user !== null && $user->admin;
        $defaultGroup = $user?->defaultGroup ?? 0;
        $groupIds = $user !== null ? $this->users->groupIdsOfUser($userId) : [];

        $content = [];
        foreach ($this->repository->searchContent($query, $userId, $defaultGroup, $groupIds, $isAdmin) as $hit) {
            if ($this->access->canRead($hit['pageId'], $userId)) {
                $content[] = [
                    'contentId' => $hit['id'],
                    'pageId' => $hit['pageId'],
                    'title' => $hit['title'],
                    'path' => $this->paths->pathTo($hit['pageId']),
                ];
            }
        }

        $pages = [];
        foreach ($this->repository->searchPages($query) as $hit) {
            if ($this->access->canRead($hit['id'], $userId)) {
                $pages[] = [
                    'pageId' => $hit['id'],
                    'path' => $this->paths->pathTo($hit['id']),
                ];
            }
        }

        $files = [];
        foreach ($this->repository->searchFiles($query) as $hit) {
            if ($this->access->canRead($hit['pageId'], $userId)) {
                $files[] = [
                    'pageId' => $hit['pageId'],
                    'filename' => $hit['filename'],
                    'filesize' => $hit['filesize'],
                    'date' => $hit['date'],
                    'path' => $this->paths->pathTo($hit['pageId']),
                ];
            }
        }

        return SearchOutcome::results($content, $pages, $files);
    }
}
