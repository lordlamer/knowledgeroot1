<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Page;

/**
 * Page permission model: rights are 0 (none), 1 (read), 2 (read+write).
 */
interface PageAccess
{
    /**
     * effective rights of a user on a single page (owner / group /
     * others rights plus entries from the access table, admins get 2)
     */
    public function rights(int $pageId, int $userId): int;

    /**
     * true when the user may read the page and every ancestor page
     */
    public function canRead(int $pageId, int $userId): bool;
}
