<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Theme;

/**
 * Finds the available themes. A theme is a directory below
 * system/themes containing an info.php that defines $theme
 * (name, css_file) - same convention as the legacy theme class.
 */
class ThemeLocator
{
    public function __construct(private readonly string $themeFolder)
    {
    }

    /**
     * @return string[] theme names
     */
    public function themeNames(): array
    {
        if (!is_dir($this->themeFolder)) {
            return [];
        }

        $names = [];
        foreach (glob(rtrim($this->themeFolder, '/\\') . '/*/info.php') ?: [] as $infoFile) {
            $theme = [];
            include $infoFile;

            if (isset($theme['name'], $theme['css_file']) && is_file(dirname($infoFile) . '/' . $theme['css_file'])) {
                $names[] = (string) $theme['name'];
            }
        }

        sort($names);

        return $names;
    }
}
