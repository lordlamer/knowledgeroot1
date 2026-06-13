<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\PageEditing;

/**
 * Raised when a page cannot be deleted because it still has subpages or
 * content. Cascading delete is intentionally not offered here.
 */
class PageNotEmpty extends \RuntimeException
{
}
