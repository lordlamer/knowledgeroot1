<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\Content\Attachment;
use Knowledgeroot\Domain\Content\AttachmentRepository;
use Knowledgeroot\Domain\Content\FileDownload;

/**
 * File attachments are stored base64 encoded in the files.file column.
 * New uploads are written with a 'base64:' prefix so the encoding is the
 * same on every SQL driver; legacy sqlite rows without the prefix are
 * decoded as plain base64. The postgres large-object storage of the
 * original code is not supported here.
 */
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

    public function findDownload(int $fileId): ?FileDownload
    {
        $row = $this->connection->fetchAssociative(
            'SELECT f.id, f.belongs_to, f.filename, f.filetype, f.file, c.belongs_to AS page_id
             FROM files f JOIN content c ON f.belongs_to = c.id
             WHERE f.id = ? AND f.deleted = 0',
            [$fileId]
        );

        if ($row === false) {
            return null;
        }

        return new FileDownload(
            fileId: (int) $row['id'],
            pageId: (int) $row['page_id'],
            filename: (string) $row['filename'],
            mimeType: (string) ($row['filetype'] ?: 'application/octet-stream'),
            bytes: $this->decode((string) $row['file']),
        );
    }

    public function incrementCounter(int $fileId): void
    {
        $this->connection->executeStatement('UPDATE files SET counter = counter + 1 WHERE id = ?', [$fileId]);
    }

    public function contentIdOf(int $fileId): ?int
    {
        $id = $this->connection->fetchOne('SELECT belongs_to FROM files WHERE id = ? AND deleted = 0', [$fileId]);

        return $id === false ? null : (int) $id;
    }

    public function store(int $contentId, string $filename, string $mimeType, int $size, string $bytes, int $owner): int
    {
        $this->connection->insert('files', [
            'belongs_to' => $contentId,
            'file' => 'base64:' . base64_encode($bytes),
            'filename' => $filename,
            'filesize' => $size,
            'filetype' => $mimeType,
            'owner' => $owner,
            'date' => date('Y-m-d H:i:s'),
            'counter' => 0,
            'deleted' => 0,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function softDelete(int $fileId): void
    {
        $this->connection->update('files', ['deleted' => 1], ['id' => $fileId]);
    }

    private function decode(string $stored): string
    {
        if (str_starts_with($stored, 'base64:')) {
            return base64_decode(substr($stored, 7), true) ?: '';
        }

        return base64_decode($stored, true) ?: '';
    }
}
