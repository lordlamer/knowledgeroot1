<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Extension;

/**
 * A registry entry in the extensions table.
 */
class Extension
{
    public function __construct(
        public readonly string $keyname,
        public readonly bool $active,
        public readonly bool $admin,
        public readonly string $version,
    ) {
    }
}
