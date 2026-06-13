<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\Admin;

use Knowledgeroot\Domain\Extension\ExtensionRepository;

/**
 * Removes an extension from the registry. The files on disk are left
 * untouched, so it can be re-installed later.
 */
class UninstallExtension
{
    public function __construct(private readonly ExtensionRepository $registry)
    {
    }

    public function execute(string $keyname): bool
    {
        if ($this->registry->find($keyname) === null) {
            return false;
        }

        $this->registry->remove($keyname);

        return true;
    }
}
