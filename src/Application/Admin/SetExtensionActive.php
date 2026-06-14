<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\Admin;

use Knowledgeroot\Domain\Extension\ExtensionRepository;

class SetExtensionActive
{
    public function __construct(private readonly ExtensionRepository $registry)
    {
    }

    public function execute(string $keyname, bool $active): bool
    {
        if ($this->registry->find($keyname) === null) {
            return false;
        }

        $this->registry->setActive($keyname, $active);

        return true;
    }
}
