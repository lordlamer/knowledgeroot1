<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\PageEditing;

use Knowledgeroot\Domain\Page\EditablePage;
use Knowledgeroot\Domain\Page\PageAccess;
use Knowledgeroot\Domain\Page\PageRepository;
use Knowledgeroot\Domain\User\UserRepository;

/**
 * Use case: create a page. A child page requires write rights on the
 * parent; a root page (parent 0) requires an administrator. The rights
 * model mirrors content creation: privileged editors set them
 * explicitly, others get their defaults, guests the public default.
 */
class CreatePage
{
    public function __construct(
        private readonly PageRepository $pages,
        private readonly PageAccess $pageAccess,
        private readonly UserRepository $users,
    ) {
    }

    /**
     * @return int id of the new page
     * @throws AccessDenied
     * @throws InvalidPageData
     */
    public function execute(int $parentId, int $userId, bool $isAdmin, bool $canEditRights, PageFormData $form): int
    {
        if ($parentId === 0) {
            if (!$isAdmin) {
                throw new AccessDenied('Only administrators may create root pages!');
            }
        } elseif ($this->pageAccess->rights($parentId, $userId) !== 2) {
            throw new AccessDenied('You are not allowed to add a page here!');
        }

        if (trim($form->title) === '') {
            throw new InvalidPageData('Name must not be empty!');
        }

        [$user, $group, $other] = $this->resolveRights($userId, $canEditRights, $form);

        $page = new EditablePage(
            id: 0,
            belongsTo: $parentId,
            title: $form->title,
            tooltip: $form->tooltip,
            symlink: $form->symlink,
            icon: $form->icon,
            defaultContentPosition: $form->defaultContentPosition,
            contentCollapsed: $form->contentCollapsed,
            owner: $userId,
            group: $canEditRights ? $form->group : $this->defaultGroup($userId),
            userRights: $user,
            groupRights: $group,
            otherRights: $other,
        );

        return $this->pages->add($page);
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function resolveRights(int $userId, bool $canEditRights, PageFormData $form): array
    {
        if ($canEditRights) {
            return [$form->userRights, $form->groupRights, $form->otherRights];
        }

        $user = $userId > 0 ? $this->users->findById($userId) : null;
        if ($user === null) {
            return [2, 2, 2];
        }

        $digits = str_pad((string) $user->defaultRights, 3, '0', STR_PAD_LEFT);

        return [(int) $digits[0], (int) $digits[1], (int) $digits[2]];
    }

    private function defaultGroup(int $userId): int
    {
        $user = $userId > 0 ? $this->users->findById($userId) : null;

        return $user?->defaultGroup ?? 0;
    }
}
