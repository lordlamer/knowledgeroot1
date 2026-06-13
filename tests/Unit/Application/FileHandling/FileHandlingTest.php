<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Application\FileHandling;

use Knowledgeroot\Application\FileHandling\AccessDenied;
use Knowledgeroot\Application\FileHandling\DeleteFile;
use Knowledgeroot\Application\FileHandling\DownloadFile;
use Knowledgeroot\Application\FileHandling\FileError;
use Knowledgeroot\Application\FileHandling\FileTooLarge;
use Knowledgeroot\Application\FileHandling\UploadedFileData;
use Knowledgeroot\Application\FileHandling\UploadFile;
use Knowledgeroot\Domain\Content\Attachment;
use Knowledgeroot\Domain\Content\AttachmentRepository;
use Knowledgeroot\Domain\Content\ContentAccess;
use Knowledgeroot\Domain\Content\EditableContent;
use Knowledgeroot\Domain\Content\FileDownload;
use Knowledgeroot\Domain\Page\PageAccess;
use Knowledgeroot\Tests\Doubles\InMemoryContentRepository;
use PHPUnit\Framework\TestCase;

class FakeAttachments implements AttachmentRepository
{
    public ?FileDownload $download = null;
    public ?int $contentId = null;
    public int $counterIncrements = 0;
    public array $stored = [];
    public array $deleted = [];

    public function findByContent(int $contentId): array
    {
        return [];
    }

    public function findDownload(int $fileId): ?FileDownload
    {
        return $this->download;
    }

    public function incrementCounter(int $fileId): void
    {
        $this->counterIncrements++;
    }

    public function contentIdOf(int $fileId): ?int
    {
        return $this->contentId;
    }

    public function store(int $contentId, string $filename, string $mimeType, int $size, string $bytes, int $owner): int
    {
        $this->stored[] = compact('contentId', 'filename', 'mimeType', 'size', 'owner');

        return 42;
    }

    public function softDelete(int $fileId): void
    {
        $this->deleted[] = $fileId;
    }
}

class AllowPages implements PageAccess
{
    public function __construct(private readonly bool $allow)
    {
    }

    public function rights(int $pageId, int $userId): int
    {
        return $this->allow ? 1 : 0;
    }

    public function canRead(int $pageId, int $userId): bool
    {
        return $this->allow;
    }
}

class MapContent implements ContentAccess
{
    public function __construct(private readonly int $rights)
    {
    }

    public function rights(int $contentId, int $userId): int
    {
        return $this->rights;
    }
}

class FileHandlingTest extends TestCase
{
    private FakeAttachments $files;

    protected function setUp(): void
    {
        $this->files = new FakeAttachments();
    }

    private function contentsWith(int $contentId, int $pageId): InMemoryContentRepository
    {
        $repo = new InMemoryContentRepository();
        $repo->store[$contentId] = new EditableContent($contentId, $pageId, 't', 'h', 1, 0, 2, 2, 2);

        return $repo;
    }

    // --- download ----------------------------------------------------------

    public function testDownloadReturnsFileWhenReadableAndCountsIt(): void
    {
        $this->files->download = new FileDownload(7, 3, 'a.pdf', 'application/pdf', 'BYTES');
        $download = new DownloadFile($this->files, new AllowPages(true));

        $result = $download->execute(7, 5);

        $this->assertNotNull($result);
        $this->assertSame('BYTES', $result->bytes);
        $this->assertSame(1, $this->files->counterIncrements);
    }

    public function testDownloadDeniedWhenPageNotReadable(): void
    {
        $this->files->download = new FileDownload(7, 3, 'a.pdf', 'application/pdf', 'BYTES');
        $download = new DownloadFile($this->files, new AllowPages(false));

        $this->assertNull($download->execute(7, 5));
        $this->assertSame(0, $this->files->counterIncrements);
    }

    public function testDownloadMissingFileReturnsNull(): void
    {
        $download = new DownloadFile($this->files, new AllowPages(true));

        $this->assertNull($download->execute(7, 5));
    }

    // --- upload ------------------------------------------------------------

    private function upload(int $rights, int $max = 0): UploadFile
    {
        return new UploadFile($this->files, $this->contentsWith(10, 3), new MapContent($rights), $max);
    }

    public function testUploadRequiresContentWriteRight(): void
    {
        $this->expectException(AccessDenied::class);
        $this->upload(1)->execute(10, 5, new UploadedFileData('a.txt', 'text/plain', 10, 'data'));
    }

    public function testUploadStoresFileAndReturnsPageId(): void
    {
        $pageId = $this->upload(2)->execute(10, 5, new UploadedFileData('a.txt', 'text/plain', 4, 'data'));

        $this->assertSame(3, $pageId);
        $this->assertCount(1, $this->files->stored);
        $this->assertSame('a.txt', $this->files->stored[0]['filename']);
    }

    public function testUploadRejectsTooLargeFile(): void
    {
        $this->expectException(FileTooLarge::class);
        $this->upload(2, max: 100)->execute(10, 5, new UploadedFileData('big.bin', 'application/octet-stream', 200, 'x'));
    }

    public function testUploadRejectsEmptyUpload(): void
    {
        $this->expectException(FileError::class);
        $this->upload(2)->execute(10, 5, new UploadedFileData('', '', 0, ''));
    }

    // --- delete ------------------------------------------------------------

    public function testDeleteRequiresContentWriteRight(): void
    {
        $this->files->contentId = 10;
        $delete = new DeleteFile($this->files, $this->contentsWith(10, 3), new MapContent(1));

        $this->expectException(AccessDenied::class);
        $delete->execute(7, 5);
    }

    public function testDeleteMissingFileThrows(): void
    {
        $this->files->contentId = null;
        $delete = new DeleteFile($this->files, $this->contentsWith(10, 3), new MapContent(2));

        $this->expectException(FileError::class);
        $delete->execute(7, 5);
    }

    public function testDeleteSoftDeletesAndReturnsPageId(): void
    {
        $this->files->contentId = 10;
        $delete = new DeleteFile($this->files, $this->contentsWith(10, 3), new MapContent(2));

        $pageId = $delete->execute(7, 5);

        $this->assertSame(3, $pageId);
        $this->assertSame([7], $this->files->deleted);
    }
}
