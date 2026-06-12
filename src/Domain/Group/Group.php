<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Group;

class Group
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly bool $enabled,
    ) {
    }
}
