<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Extension;

/**
 * Discovers the extensions available on disk by scanning the three
 * extension folders for an info.php (which defines a $CONF array). An
 * extension under system/sysext is an admin extension.
 */
class ExtensionCatalog
{
    /**
     * folder (relative to base) => isAdmin
     *
     * @var array<string, bool>
     */
    private const FOLDERS = [
        'extension/' => false,
        'system/extension/' => false,
        'system/sysext/' => true,
    ];

    public function __construct(private readonly string $basePath)
    {
    }

    /**
     * @return array<string, array{title: string, description: string, version: string, state: string, folder: string, admin: bool}>
     */
    public function available(): array
    {
        $found = [];

        foreach (self::FOLDERS as $folder => $isAdmin) {
            foreach (glob($this->basePath . $folder . '*/info.php') ?: [] as $infoFile) {
                $keyname = basename(dirname($infoFile));
                if (isset($found[$keyname])) {
                    continue;
                }

                $CONF = [];
                include $infoFile;

                $found[$keyname] = [
                    'title' => (string) ($CONF['title'] ?? $keyname),
                    'description' => (string) ($CONF['description'] ?? ''),
                    'version' => (string) ($CONF['version'] ?? ''),
                    'state' => (string) ($CONF['state'] ?? ''),
                    'folder' => $folder,
                    'admin' => $isAdmin,
                ];
            }
        }

        ksort($found);

        return $found;
    }
}
