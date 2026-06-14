<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Search;

/**
 * Splits a search string into and / or / not conditions.
 *
 * Supported syntax (same as the old Search_String_Parser):
 *   foo bar          - both words have to match (and)
 *   foo OR bar       - one of the words has to match (or group)
 *   -foo             - word must not match (not)
 *   "foo bar"        - phrase, also with - prefix and in OR groups
 */
class SearchQueryParser
{
    private const OR_WORD = 'OR';
    private const QUOTE = '"';
    private const NOT_PREFIX = '-';

    public function parse(string $searchString): SearchQuery
    {
        $and = [];
        $or = [];
        $not = [];

        $tokens = preg_split('/\s+/', trim($searchString), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $lastToken = null;
        $orGroup = 0;
        $wordGroup = '';
        $inWordGroup = false;
        $doNotAdd = false;
        $wordGroupNot = false;

        foreach ($tokens as $key => $token) {
            $nextToken = $tokens[$key + 1] ?? null;

            // closing quote of a phrase
            if ($inWordGroup && mb_substr($token, -1) === self::QUOTE) {
                $wordGroup .= ' ' . mb_substr($token, 0, mb_strlen($token) - 1);
                $inWordGroup = false;

                if ($nextToken === self::OR_WORD || $lastToken === self::OR_WORD) {
                    $or[$orGroup][] = $wordGroup;
                } elseif ($wordGroupNot) {
                    $not[] = $wordGroup;
                    $wordGroupNot = false;
                } else {
                    $and[] = $wordGroup;
                }

                $doNotAdd = true;
                $lastToken = $wordGroup;

                if ($nextToken !== self::OR_WORD && $lastToken !== self::OR_WORD) {
                    $orGroup++;
                }
                if ($lastToken === self::OR_WORD && $nextToken !== self::OR_WORD) {
                    $orGroup++;
                }
            }

            // inside a phrase
            if ($inWordGroup) {
                $wordGroup .= ' ' . $token;
                $doNotAdd = true;
            }

            // phrase start
            if (mb_substr($token, 0, 1) === self::QUOTE) {
                $wordGroup = mb_substr($token, 1);
                $inWordGroup = true;
                $doNotAdd = true;
            }

            // negated phrase start
            if (mb_substr($token, 0, 2) === self::NOT_PREFIX . self::QUOTE) {
                $wordGroup = mb_substr($token, 2);
                $inWordGroup = true;
                $wordGroupNot = true;
                $doNotAdd = true;
            }

            // normal word
            if ($token !== self::OR_WORD && !$inWordGroup && !$doNotAdd) {
                if ($nextToken === self::OR_WORD || $lastToken === self::OR_WORD) {
                    $or[$orGroup][] = $token;
                } elseif (mb_substr($token, 0, 1) === self::NOT_PREFIX) {
                    $not[] = mb_substr($token, 1);
                } else {
                    $and[] = $token;
                }

                if ($lastToken === self::OR_WORD && $nextToken !== self::OR_WORD) {
                    $orGroup++;
                }
                if ($nextToken !== self::OR_WORD && $lastToken !== self::OR_WORD) {
                    $orGroup++;
                }
            }

            if (!$doNotAdd) {
                $lastToken = $token;
            }

            $doNotAdd = false;
        }

        return new SearchQuery($and, array_values($or), $not);
    }
}
