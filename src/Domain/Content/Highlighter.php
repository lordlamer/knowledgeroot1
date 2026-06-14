<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Content;

/**
 * Wraps search terms in highlight markup, but only inside the text of
 * the html - never inside tags or attributes - so existing markup and
 * links stay intact. Replacement of the legacy highlight class for the
 * search-result view of a page.
 */
class Highlighter
{
    /**
     * @param string[] $terms
     */
    public function highlight(string $html, array $terms, string $cssClass = 'highlightword'): string
    {
        $terms = array_values(array_filter(array_map('trim', $terms), static fn (string $t) => $t !== ''));
        if ($terms === []) {
            return $html;
        }

        $patterns = array_map(
            static fn (string $term) => preg_quote($term, '/'),
            $terms
        );
        $regex = '/(' . implode('|', $patterns) . ')/iu';

        // split into tags and text; odd indexes are tags (kept verbatim)
        $segments = preg_split('/(<[^>]*>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($segments === false) {
            return $html;
        }

        $out = '';
        foreach ($segments as $index => $segment) {
            if ($index % 2 === 1) {
                // a tag - leave untouched
                $out .= $segment;
                continue;
            }

            $out .= preg_replace($regex, '<span class="' . $cssClass . '">$1</span>', $segment);
        }

        return $out;
    }
}
