<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Http;

/**
 * Builds base-path aware urls so routes and assets work for
 * installations in the document root as well as in sub directories.
 */
class UrlHelper
{
    public function basePath(): string
    {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));

        return $scriptDir === '/' ? '' : rtrim($scriptDir, '/');
    }

    /**
     * absolute path for a route or asset, e.g. to('users') -> /users
     */
    public function to(string $path): string
    {
        return $this->basePath() . '/' . ltrim($path, '/');
    }
}
