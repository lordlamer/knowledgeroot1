<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Content;

/**
 * Cleans untrusted content HTML so stored markup cannot carry scripts
 * or other active content when rendered.
 */
interface HtmlSanitizer
{
    public function sanitize(string $html): string;
}
