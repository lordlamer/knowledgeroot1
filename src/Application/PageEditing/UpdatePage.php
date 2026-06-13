<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\PageEditing;

use Knowledgeroot\Domain\Page\EditablePage;
use Knowledgeroot\Domain\Page\PageAccess;
use Knowledgeroot\Domain\Page\PageRepository;

/**
 * Use case: edit a page. Requires write rights on the page. Privileged
 * editors (admin / rightedit) may also change the rights; for everyone
 * else the rights stay as they are.
 */
class UpdatePage
{
    public function __construct(
        private readonly PageRepository $pages,
        private readonly PageAccess $pageAccess,
    ) {
    }

    /**
     * @throws PageNotFound
     * @throws AccessDenied
     * @throws InvalidPageData
     */
    public function execute(int $pageId, int $userId, bool $canEditRights, PageFormData $form): void
    {
        $existing = $this->pages->findEditable($pageId);
        if ($existing === null) {
            throw new PageNotFound('Page not found!');
        }

        if ($this->pageAccess->rights($pageId, $userId) !== 2) {
            throw new AccessDenied('You are not allowed to edit this page!');
        }

        if (trim($form->title) === '') {
            throw new InvalidPageData('Name must not be empty!');
        }

        $updated = new EditablePage(
            id: $existing->id,
            belongsTo: $existing->belongsTo,
            title: $form->title,
            tooltip: $form->tooltip,
            symlink: $form->symlink,
            icon: $form->icon,
            defaultContentPosition: $form->defaultContentPosition,
            contentCollapsed: $form->contentCollapsed,
            owner: $existing->owner,
            group: $canEditRights ? $form->group : $existing->group,
            userRights: $canEditRights ? $form->userRights : $existing->userRights,
            groupRights: $canEditRights ? $form->groupRights : $existing->groupRights,
            otherRights: $canEditRights ? $form->otherRights : $existing->otherRights,
        );

        $this->pages->update($updated, $canEditRights);
    }
}
