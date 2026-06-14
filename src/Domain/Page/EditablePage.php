<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Page;

/**
 * The editable state of a page (tree node): its display attributes and
 * the rights stored on the row (owner / group / user|group|other).
 *
 * Alias and the subinherit* parent-inheritance fields are not part of
 * this model - they are left untouched on update.
 */
class EditablePage
{
    public function __construct(
        public readonly int $id,
        public readonly int $belongsTo,
        public readonly string $title,
        public readonly string $tooltip,
        public readonly int $symlink,
        public readonly string $icon,
        public readonly int $defaultContentPosition,
        public readonly bool $contentCollapsed,
        public readonly int $owner,
        public readonly int $group,
        public readonly int $userRights,
        public readonly int $groupRights,
        public readonly int $otherRights,
    ) {
    }
}
