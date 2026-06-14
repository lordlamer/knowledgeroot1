<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Infrastructure\Content;

use Knowledgeroot\Infrastructure\Content\HtmlPurifierSanitizer;
use PHPUnit\Framework\TestCase;

class HtmlPurifierSanitizerTest extends TestCase
{
    private HtmlPurifierSanitizer $sanitizer;

    protected function setUp(): void
    {
        // no cache dir -> in-memory definitions, fine for tests
        $this->sanitizer = new HtmlPurifierSanitizer(sys_get_temp_dir() . '/kr-purifier-test');
    }

    public function testRemovesScriptTags(): void
    {
        $clean = $this->sanitizer->sanitize('<p>hi</p><script>alert(1)</script>');

        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringContainsString('<p>hi</p>', $clean);
    }

    public function testStripsEventHandlers(): void
    {
        $clean = $this->sanitizer->sanitize('<img src="x" onerror="alert(1)">');

        $this->assertStringNotContainsString('onerror', $clean);
    }

    public function testStripsJavascriptUrls(): void
    {
        $clean = $this->sanitizer->sanitize('<a href="javascript:alert(1)">x</a>');

        $this->assertStringNotContainsString('javascript:', $clean);
    }

    public function testKeepsSafeFormattingMarkup(): void
    {
        $html = '<p><strong>bold</strong> and <a href="https://example.com">link</a></p>';
        $clean = $this->sanitizer->sanitize($html);

        $this->assertStringContainsString('<strong>bold</strong>', $clean);
        $this->assertStringContainsString('href="https://example.com"', $clean);
    }

    public function testEmptyStringStaysEmpty(): void
    {
        $this->assertSame('', $this->sanitizer->sanitize(''));
    }
}
