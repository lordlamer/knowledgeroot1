<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\Page\Breadcrumb;
use Knowledgeroot\Domain\Page\PagePathResolver;

class DbalPagePathResolver implements PagePathResolver
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function pathTo(int $pageId): array
    {
        $path = [];
        $guard = 0;

        while ($pageId > 0 && $guard++ < 100) {
            $row = $this->connection->fetchAssociative(
                'SELECT id, belongs_to, title FROM tree WHERE id = ? AND deleted = 0',
                [$pageId]
            );

            if ($row === false) {
                break;
            }

            array_unshift($path, new Breadcrumb((int) $row['id'], (string) $row['title']));
            $pageId = (int) $row['belongs_to'];
        }

        return $path;
    }
}
