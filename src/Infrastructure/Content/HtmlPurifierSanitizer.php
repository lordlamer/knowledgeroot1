<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Content;

use HTMLPurifier;
use HTMLPurifier_Config;
use Knowledgeroot\Domain\Content\HtmlSanitizer;

/**
 * HTMLPurifier-backed sanitizer. Strips scripts, event handlers and
 * other active content while keeping standard formatting markup.
 */
class HtmlPurifierSanitizer implements HtmlSanitizer
{
    private HTMLPurifier $purifier;

    public function __construct(string $cacheDir)
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.TargetBlank', true);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);

        if (is_dir($cacheDir) && is_writable($cacheDir)) {
            $config->set('Cache.SerializerPath', $cacheDir);
        } else {
            $config->set('Cache.DefinitionImpl', null);
        }

        $this->purifier = new HTMLPurifier($config);
    }

    public function sanitize(string $html): string
    {
        if ($html === '') {
            return '';
        }

        return $this->purifier->purify($html);
    }
}
