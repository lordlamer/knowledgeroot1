<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Translation;

/**
 * Gettext .mo based translator, replacement for the previously used
 * Zend_Translate gettext adapter. Implements the API surface the legacy
 * code relies on: _(), addTranslation(), setLocale(), getLocale().
 */
class Translator
{
    /** @var array<string, array<string, string>> messages per locale */
    private array $messages = [];

    private string $locale = 'en_US';

    public function __construct(?string $moFile = null, ?string $locale = null)
    {
        if ($moFile !== null && $locale !== null) {
            $this->addTranslation($moFile, $locale);
            $this->locale = $locale;
        }
    }

    public function addTranslation(string $moFile, string $locale): void
    {
        $this->messages[$locale] = array_merge(
            $this->messages[$locale] ?? [],
            $this->parseMoFile($moFile)
        );
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Translate a message. Falls back to the message id itself when no
     * translation exists - same behaviour as Zend_Translate.
     */
    public function _(string $messageId, ?string $locale = null): string
    {
        $locale = $locale ?? $this->locale;

        if (isset($this->messages[$locale][$messageId]) && $this->messages[$locale][$messageId] !== '') {
            return $this->messages[$locale][$messageId];
        }

        // try language part only (de instead of de_DE)
        $lang = substr($locale, 0, (int) strpos($locale . '_', '_'));
        foreach ($this->messages as $loc => $msgs) {
            if (str_starts_with($loc, $lang) && isset($msgs[$messageId]) && $msgs[$messageId] !== '') {
                return $msgs[$messageId];
            }
        }

        return $messageId;
    }

    public function translate(string $messageId, ?string $locale = null): string
    {
        return $this->_($messageId, $locale);
    }

    public function isTranslated(string $messageId, ?string $locale = null): bool
    {
        $locale = $locale ?? $this->locale;

        return isset($this->messages[$locale][$messageId]);
    }

    /**
     * Minimal .mo file parser (GNU gettext binary format).
     *
     * @return array<string, string>
     */
    private function parseMoFile(string $file): array
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new \RuntimeException('Translation file not found: ' . $file);
        }

        $data = file_get_contents($file);
        if ($data === false || strlen($data) < 28) {
            return [];
        }

        $magic = unpack('V', substr($data, 0, 4))[1];
        if ($magic === 0x950412de) {
            $unpackFormat = 'V';
        } elseif ($magic === 0xde120495) {
            $unpackFormat = 'N';
        } else {
            throw new \RuntimeException('Invalid .mo file: ' . $file);
        }

        $header = unpack(
            "{$unpackFormat}revision/{$unpackFormat}count/{$unpackFormat}originalsOffset/{$unpackFormat}translationsOffset",
            substr($data, 4, 16)
        );

        $messages = [];
        for ($i = 0; $i < $header['count']; $i++) {
            $orig = unpack("{$unpackFormat}length/{$unpackFormat}offset", substr($data, $header['originalsOffset'] + $i * 8, 8));
            $trans = unpack("{$unpackFormat}length/{$unpackFormat}offset", substr($data, $header['translationsOffset'] + $i * 8, 8));

            $original = substr($data, $orig['offset'], $orig['length']);
            $translation = substr($data, $trans['offset'], $trans['length']);

            // strip plural forms / context - we only need singular lookups
            $original = explode("\0", $original)[0];
            $translation = explode("\0", $translation)[0];

            if ($original !== '') {
                $messages[$original] = $translation;
            }
        }

        return $messages;
    }
}
