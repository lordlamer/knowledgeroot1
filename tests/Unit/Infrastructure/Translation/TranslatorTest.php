<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Infrastructure\Translation;

use Knowledgeroot\Infrastructure\Translation\Translator;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    private string $moFile;

    protected function setUp(): void
    {
        $this->moFile = tempnam(sys_get_temp_dir(), 'krmo');

        file_put_contents($this->moFile, $this->buildMoFile([
            'login' => 'Anmelden',
            'user' => 'Benutzer',
        ]));
    }

    protected function tearDown(): void
    {
        @unlink($this->moFile);
    }

    /**
     * Build a minimal .mo file (GNU gettext binary format, little endian).
     *
     * @param array<string, string> $messages
     */
    private function buildMoFile(array $messages): string
    {
        $count = count($messages);
        $originalsOffset = 28;
        $translationsOffset = $originalsOffset + $count * 8;
        $stringsOffset = $translationsOffset + $count * 8;

        $originalsTable = '';
        $translationsTable = '';
        $strings = '';

        $offset = $stringsOffset;
        foreach (array_keys($messages) as $original) {
            $originalsTable .= pack('VV', strlen($original), $offset);
            $strings .= $original . "\0";
            $offset += strlen($original) + 1;
        }

        foreach ($messages as $translation) {
            $translationsTable .= pack('VV', strlen($translation), $offset);
            $strings .= $translation . "\0";
            $offset += strlen($translation) + 1;
        }

        return pack('VVVVVVV', 0x950412de, 0, $count, $originalsOffset, $translationsOffset, 0, 0)
            . $originalsTable . $translationsTable . $strings;
    }

    public function testTranslatesKnownMessages(): void
    {
        $translator = new Translator($this->moFile, 'de_DE');

        $this->assertSame('Anmelden', $translator->_('login'));
        $this->assertSame('Benutzer', $translator->_('user'));
    }

    public function testFallsBackToMessageIdForUnknownMessages(): void
    {
        $translator = new Translator($this->moFile, 'de_DE');

        $this->assertSame('does not exist', $translator->_('does not exist'));
    }

    public function testLocaleSwitching(): void
    {
        $translator = new Translator($this->moFile, 'de_DE');
        $translator->setLocale('en_US');

        $this->assertSame('en_US', $translator->getLocale());
        // no en_US catalogue loaded, but the language fallback finds the de_DE one... not for en
        $this->assertSame('login', $translator->_('login', 'fr_FR'));
        // explicit locale still works
        $this->assertSame('Anmelden', $translator->_('login', 'de_DE'));
    }

    public function testInvalidFileThrows(): void
    {
        $invalid = tempnam(sys_get_temp_dir(), 'krmo');
        file_put_contents($invalid, 'this is not a mo file at all!!');

        try {
            $this->expectException(\RuntimeException::class);
            new Translator($invalid, 'de_DE');
        } finally {
            @unlink($invalid);
        }
    }
}
