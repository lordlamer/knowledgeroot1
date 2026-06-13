<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\Content\Attachment;
use Knowledgeroot\Domain\Content\AttachmentRepository;

class DbalAttachmentRepository implements AttachmentRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function findByContent(int $contentId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, belongs_to, filename, filesize, date FROM files WHERE belongs_to = ? AND deleted = 0 ORDER BY id ASC',
            [$contentId]
        );

        return array_map(static fn (array $row) => new Attachment(
            id: (int) $row['id'],
            contentId: (int) $row['belongs_to'],
            filename: (string) $row['filename'],
            filesize: (int) $row['filesize'],
            date: (string) ($row['date'] ?? ''),
        ), $rows);
    }
}
