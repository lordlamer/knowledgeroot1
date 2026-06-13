<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\Admin;

use Knowledgeroot\Domain\Extension\ExtensionRepository;
use Knowledgeroot\Infrastructure\Extension\ExtensionCatalog;

/**
 * Registers an on-disk extension in the database (installed, inactive).
 * Only the registry is touched - no remote download and no SQL/file
 * generation (those legacy features are deliberately not carried over).
 */
class InstallExtension
{
    public function __construct(
        private readonly ExtensionRepository $registry,
        private readonly ExtensionCatalog $catalog,
    ) {
    }

    public function execute(string $keyname): bool
    {
        if ($this->registry->find($keyname) !== null) {
            return false;
        }

        $available = $this->catalog->available();
        if (!isset($available[$keyname])) {
            return false;
        }

        $this->registry->register($keyname, $available[$keyname]['admin']);

        return true;
    }
}
