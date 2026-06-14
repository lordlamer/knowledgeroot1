<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Domain\Search;

use Knowledgeroot\Domain\Search\SearchQueryParser;
use PHPUnit\Framework\TestCase;

class SearchQueryParserTest extends TestCase
{
    private SearchQueryParser $parser;

    protected function setUp(): void
    {
        $this->parser = new SearchQueryParser();
    }

    public function testPlainWordsAreAndConditions(): void
    {
        $query = $this->parser->parse('foo bar');

        $this->assertSame(['foo', 'bar'], $query->and);
        $this->assertSame([], $query->orGroups);
        $this->assertSame([], $query->not);
    }

    public function testOrKeywordBuildsOrGroup(): void
    {
        $query = $this->parser->parse('foo OR bar');

        $this->assertSame([], $query->and);
        $this->assertSame([['foo', 'bar']], $query->orGroups);
    }

    public function testMultipleOrGroups(): void
    {
        $query = $this->parser->parse('a OR b c OR d');

        $this->assertSame([['a', 'b'], ['c', 'd']], $query->orGroups);
    }

    public function testMinusPrefixIsNotCondition(): void
    {
        $query = $this->parser->parse('-foo bar');

        $this->assertSame(['bar'], $query->and);
        $this->assertSame(['foo'], $query->not);
    }

    public function testQuotedPhraseStaysTogether(): void
    {
        $query = $this->parser->parse('"hello world" baz');

        $this->assertSame(['hello world', 'baz'], $query->and);
    }

    public function testNegatedQuotedPhrase(): void
    {
        $query = $this->parser->parse('-"hello world" foo');

        $this->assertSame(['foo'], $query->and);
        $this->assertSame(['hello world'], $query->not);
    }

    public function testEmptyStringGivesEmptyQuery(): void
    {
        $this->assertTrue($this->parser->parse('')->isEmpty());
        $this->assertTrue($this->parser->parse('   ')->isEmpty());
    }

    public function testMixedQuery(): void
    {
        $query = $this->parser->parse('install -windows linux OR debian');

        $this->assertSame(['install'], $query->and);
        $this->assertSame(['windows'], $query->not);
        $this->assertSame([['linux', 'debian']], $query->orGroups);
    }
}
