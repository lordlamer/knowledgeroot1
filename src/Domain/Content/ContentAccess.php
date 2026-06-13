<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Content;

/**
 * Permission model for a single content block: rights are 0 (none),
 * 1 (read), 2 (read+write). Owner gets userrights, a matching group
 * gets grouprights, everyone else otherrights; admins always get 2.
 */
interface ContentAccess
{
    public function rights(int $contentId, int $userId): int;
}
