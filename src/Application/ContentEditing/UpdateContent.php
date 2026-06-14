<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\ContentEditing;

use Knowledgeroot\Domain\Content\ContentAccess;
use Knowledgeroot\Domain\Content\ContentRepository;
use Knowledgeroot\Domain\Content\EditableContent;

/**
 * Use case: edit a content block. Requires write rights on the block.
 * Privileged editors (admin / rightedit) may also change the rights;
 * for everyone else only the text and title are updated and the
 * existing rights stay as they are.
 */
class UpdateContent
{
    public function __construct(
        private readonly ContentRepository $contents,
        private readonly ContentAccess $contentAccess,
    ) {
    }

    /**
     * @return int the page id the block belongs to
     * @throws ContentNotFound
     * @throws AccessDenied
     */
    public function execute(int $contentId, int $userId, bool $canEditRights, ContentFormData $form): int
    {
        $existing = $this->contents->findEditable($contentId);
        if ($existing === null) {
            throw new ContentNotFound('Content not found!');
        }

        if ($this->contentAccess->rights($contentId, $userId) !== 2) {
            throw new AccessDenied('You are not allowed to edit this content!');
        }

        $updated = new EditableContent(
            id: $existing->id,
            pageId: $existing->pageId,
            title: $form->title,
            html: $form->html,
            owner: $existing->owner,
            group: $canEditRights ? $form->group : $existing->group,
            userRights: $canEditRights ? $form->userRights : $existing->userRights,
            groupRights: $canEditRights ? $form->groupRights : $existing->groupRights,
            otherRights: $canEditRights ? $form->otherRights : $existing->otherRights,
        );

        $this->contents->update($updated, $userId, $canEditRights);

        return $existing->pageId;
    }
}
