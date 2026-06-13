<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Domain\Content;

use Knowledgeroot\Domain\Content\Highlighter;
use PHPUnit\Framework\TestCase;

class HighlighterTest extends TestCase
{
    private Highlighter $highlighter;

    protected function setUp(): void
    {
        $this->highlighter = new Highlighter();
    }

    public function testHighlightsPlainText(): void
    {
        $result = $this->highlighter->highlight('the quick brown fox', ['quick']);

        $this->assertSame('the <span class="highlightword">quick</span> brown fox', $result);
    }

    public function testIsCaseInsensitiveButKeepsOriginalCasing(): void
    {
        $result = $this->highlighter->highlight('Hello WORLD', ['world']);

        $this->assertSame('Hello <span class="highlightword">WORLD</span>', $result);
    }

    public function testDoesNotHighlightInsideTagsOrAttributes(): void
    {
        $html = '<a href="http://foo/quick" class="quick">quick</a>';

        $result = $this->highlighter->highlight($html, ['quick']);

        // the attribute and href stay untouched, only the link text is wrapped
        $this->assertSame('<a href="http://foo/quick" class="quick"><span class="highlightword">quick</span></a>', $result);
    }

    public function testHighlightsMultipleTerms(): void
    {
        $result = $this->highlighter->highlight('foo and bar', ['foo', 'bar']);

        $this->assertSame('<span class="highlightword">foo</span> and <span class="highlightword">bar</span>', $result);
    }

    public function testEmptyTermsReturnInputUnchanged(): void
    {
        $this->assertSame('unchanged', $this->highlighter->highlight('unchanged', []));
        $this->assertSame('unchanged', $this->highlighter->highlight('unchanged', ['', '  ']));
    }

    public function testRegexSpecialCharactersAreEscaped(): void
    {
        $result = $this->highlighter->highlight('a+b and c.d', ['a+b']);

        $this->assertSame('<span class="highlightword">a+b</span> and c.d', $result);
    }
}
