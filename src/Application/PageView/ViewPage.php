<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\PageView;

use Knowledgeroot\Domain\Content\AttachmentRepository;
use Knowledgeroot\Domain\Content\ContentAccess;
use Knowledgeroot\Domain\Content\ContentRepository;
use Knowledgeroot\Domain\Content\Highlighter;
use Knowledgeroot\Domain\Page\PageAccess;
use Knowledgeroot\Domain\Page\PagePathResolver;
use Knowledgeroot\Domain\Page\PageRepository;

/**
 * Use case: read a page for display - breadcrumb, the readable content
 * blocks with their attachments, and the viewer's edit rights so the
 * presentation can decide which actions to offer.
 *
 * Returns null when the page does not exist or the viewer may not read
 * it (the page or any of its ancestors).
 */
class ViewPage
{
    public function __construct(
        private readonly PageRepository $pages,
        private readonly ContentRepository $contents,
        private readonly AttachmentRepository $attachments,
        private readonly PageAccess $pageAccess,
        private readonly ContentAccess $contentAccess,
        private readonly PagePathResolver $paths,
        private readonly Highlighter $highlighter,
    ) {
    }

    /**
     * @param string[] $highlightTerms
     */
    public function execute(int $pageId, int $userId, array $highlightTerms = []): ?PageView
    {
        $page = $this->pages->findById($pageId);
        if ($page === null || !$this->pageAccess->canRead($pageId, $userId)) {
            return null;
        }

        $pageRights = $this->pageAccess->rights($pageId, $userId);

        $blocks = [];
        foreach ($this->contents->findByPage($pageId) as $block) {
            $contentRights = $this->contentAccess->rights($block->id, $userId);
            if ($contentRights === 0) {
                continue;
            }

            // only text blocks are rendered here; typed (extension) blocks
            // are skipped until the extension rendering is migrated
            if (!$block->isText()) {
                continue;
            }

            $html = $highlightTerms === []
                ? $block->html
                : $this->highlighter->highlight($block->html, $highlightTerms);

            $blocks[] = new ViewedContentBlock(
                block: $block,
                html: $html,
                canEdit: $contentRights === 2 && $pageRights === 2,
                attachments: $this->attachments->findByContent($block->id),
            );
        }

        return new PageView(
            page: $page,
            breadcrumb: $this->paths->pathTo($pageId),
            blocks: $blocks,
            canEditPage: $pageRights === 2,
        );
    }
}
