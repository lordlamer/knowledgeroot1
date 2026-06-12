<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Language;

/**
 * Finds the installed interface languages: directories like de_DE.UTF8
 * below system/language - same convention as the legacy language class.
 */
class LanguageLocator
{
    public function __construct(private readonly string $languageFolder)
    {
    }

    /**
     * @return string[] locale directory names, e.g. ["de_DE.UTF8", "en_US.UTF8"]
     */
    public function localeNames(): array
    {
        if (!is_dir($this->languageFolder)) {
            return [];
        }

        $locales = [];
        foreach (scandir($this->languageFolder) ?: [] as $entry) {
            if ($entry !== '.' && $entry !== '..' && is_dir(rtrim($this->languageFolder, '/\\') . '/' . $entry)) {
                $locales[] = $entry;
            }
        }

        sort($locales);

        return $locales;
    }
}
