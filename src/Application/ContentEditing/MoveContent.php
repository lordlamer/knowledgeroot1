<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\ContentEditing;

use Knowledgeroot\Domain\Content\ContentRepository;
use Knowledgeroot\Domain\Page\PageAccess;

/**
 * Use case: reorder a content block within its page by one step up or
 * down. Requires write rights on the page. The whole page is re-indexed
 * with sequential sorting values, which also normalises any legacy
 * null/duplicate sortings.
 */
class MoveContent
{
    public const UP = 'up';
    public const DOWN = 'down';

    public function __construct(
        private readonly ContentRepository $contents,
        private readonly PageAccess $pageAccess,
    ) {
    }

    /**
     * @return int the page id the block belongs to
     * @throws ContentNotFound
     * @throws AccessDenied
     */
    public function execute(int $contentId, int $userId, string $direction): int
    {
        $content = $this->contents->findEditable($contentId);
        if ($content === null) {
            throw new ContentNotFound('Content not found!');
        }

        if ($this->pageAccess->rights($content->pageId, $userId) !== 2) {
            throw new AccessDenied('You are not allowed to move this content!');
        }

        $ids = $this->contents->orderedIdsByPage($content->pageId);
        $index = array_search($contentId, $ids, true);

        if ($index === false) {
            return $content->pageId;
        }

        $swapWith = $direction === self::UP ? $index - 1 : $index + 1;

        if ($swapWith < 0 || $swapWith >= count($ids)) {
            // already at the top / bottom - nothing to do
            return $content->pageId;
        }

        [$ids[$index], $ids[$swapWith]] = [$ids[$swapWith], $ids[$index]];

        foreach ($ids as $position => $id) {
            $this->contents->setSorting($id, $position);
        }

        return $content->pageId;
    }
}
