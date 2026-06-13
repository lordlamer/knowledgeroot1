<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\ContentEditing;

use Knowledgeroot\Domain\Content\ContentRepository;
use Knowledgeroot\Domain\Content\EditableContent;
use Knowledgeroot\Domain\Page\PageAccess;
use Knowledgeroot\Domain\User\UserRepository;

/**
 * Use case: add a content block to a page. Requires write rights on the
 * page. Privileged editors (admin or the rightedit flag) set the rights
 * explicitly; everyone else gets their own default group/rights, guests
 * get the public default (read+write for all) - mirroring the legacy
 * new_content behaviour for the common, non-inherited case.
 */
class CreateContent
{
    public function __construct(
        private readonly ContentRepository $contents,
        private readonly PageAccess $pageAccess,
        private readonly UserRepository $users,
    ) {
    }

    /**
     * @return int id of the new content block
     * @throws AccessDenied
     */
    public function execute(int $pageId, int $userId, bool $canEditRights, ContentFormData $form): int
    {
        if ($this->pageAccess->rights($pageId, $userId) !== 2) {
            throw new AccessDenied('You are not allowed to add content here!');
        }

        $content = new EditableContent(
            id: 0,
            pageId: $pageId,
            title: $form->title,
            html: $form->html,
            owner: $userId,
            group: $canEditRights ? $form->group : $this->defaultGroup($userId),
            userRights: $canEditRights ? $form->userRights : $this->defaultRights($userId)[0],
            groupRights: $canEditRights ? $form->groupRights : $this->defaultRights($userId)[1],
            otherRights: $canEditRights ? $form->otherRights : $this->defaultRights($userId)[2],
        );

        return $this->contents->add($content, $this->contents->nextSorting($pageId), $userId);
    }

    private function defaultGroup(int $userId): int
    {
        $user = $userId > 0 ? $this->users->findById($userId) : null;

        return $user?->defaultGroup ?? 0;
    }

    /**
     * @return array{0: int, 1: int, 2: int} user, group, other rights
     */
    private function defaultRights(int $userId): array
    {
        $user = $userId > 0 ? $this->users->findById($userId) : null;

        // guests / unknown users: public default read+write (legacy 2/2/2)
        if ($user === null) {
            return [2, 2, 2];
        }

        $digits = str_pad((string) $user->defaultRights, 3, '0', STR_PAD_LEFT);

        return [(int) $digits[0], (int) $digits[1], (int) $digits[2]];
    }
}
