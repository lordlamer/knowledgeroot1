<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Doubles;

use Knowledgeroot\Domain\Content\HtmlSanitizer;

/**
 * Test double that returns the HTML unchanged, so use-case tests can
 * assert on exact markup without depending on HTMLPurifier's output.
 */
class PassthroughHtmlSanitizer implements HtmlSanitizer
{
    public function sanitize(string $html): string
    {
        return $html;
    }
}
