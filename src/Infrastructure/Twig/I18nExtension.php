<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Twig;

use Knowledgeroot\Infrastructure\Translation\Translator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Twig i18n bridge, replacement for the abandoned twig/extensions
 * I18n extension. Exposes the translator as 'trans' filter and function:
 *
 *   {{ 'login'|trans }}
 *   {{ trans('login') }}
 */
class I18nExtension extends AbstractExtension
{
    public function __construct(private readonly Translator $translator)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('trans', $this->translator->_(...)),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('trans', $this->translator->_(...)),
        ];
    }
}
