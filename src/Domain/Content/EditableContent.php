<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Content;

/**
 * The editable state of a content block: the text and the rights stored
 * on the content row itself (owner / group / user|group|other rights).
 *
 * The per-entry access table and the parent-inheritance flags are not
 * part of this model - they are left untouched on update.
 */
class EditableContent
{
    public function __construct(
        public readonly int $id,
        public readonly int $pageId,
        public readonly string $title,
        public readonly string $html,
        public readonly int $owner,
        public readonly int $group,
        public readonly int $userRights,
        public readonly int $groupRights,
        public readonly int $otherRights,
    ) {
    }
}
