<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\Admin;

use Knowledgeroot\Domain\Extension\ExtensionRepository;
use Knowledgeroot\Infrastructure\Extension\ExtensionCatalog;

/**
 * Merges the extensions registered in the database with the extensions
 * found on disk into one overview.
 */
class ListExtensions
{
    public function __construct(
        private readonly ExtensionRepository $registry,
        private readonly ExtensionCatalog $catalog,
    ) {
    }

    /**
     * @return array<int, array{keyname: string, title: string, description: string, version: string, state: string, admin: bool, installed: bool, active: bool, onDisk: bool}>
     */
    public function execute(): array
    {
        $registered = $this->registry->findAll();
        $available = $this->catalog->available();

        $keynames = array_unique([...array_keys($registered), ...array_keys($available)]);
        sort($keynames);

        $items = [];
        foreach ($keynames as $keyname) {
            $reg = $registered[$keyname] ?? null;
            $disk = $available[$keyname] ?? null;

            $items[] = [
                'keyname' => $keyname,
                'title' => $disk['title'] ?? $keyname,
                'description' => $disk['description'] ?? '',
                'version' => $disk['version'] ?? ($reg?->version ?? ''),
                'state' => $disk['state'] ?? '',
                'admin' => $disk['admin'] ?? ($reg?->admin ?? false),
                'installed' => $reg !== null,
                'active' => $reg?->active ?? false,
                'onDisk' => $disk !== null,
            ];
        }

        return $items;
    }
}
