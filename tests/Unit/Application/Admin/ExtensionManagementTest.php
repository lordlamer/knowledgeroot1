<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Application\Admin;

use Knowledgeroot\Application\Admin\InstallExtension;
use Knowledgeroot\Application\Admin\ListExtensions;
use Knowledgeroot\Application\Admin\SetExtensionActive;
use Knowledgeroot\Application\Admin\UninstallExtension;
use Knowledgeroot\Domain\Extension\Extension;
use Knowledgeroot\Domain\Extension\ExtensionRepository;
use Knowledgeroot\Infrastructure\Extension\ExtensionCatalog;
use PHPUnit\Framework\TestCase;

class InMemoryExtensionRepository implements ExtensionRepository
{
    /** @var array<string, Extension> */
    public array $store = [];

    public function findAll(): array
    {
        return $this->store;
    }

    public function find(string $keyname): ?Extension
    {
        return $this->store[$keyname] ?? null;
    }

    public function setActive(string $keyname, bool $active): void
    {
        $e = $this->store[$keyname];
        $this->store[$keyname] = new Extension($e->keyname, $active, $e->admin, $e->version);
    }

    public function register(string $keyname, bool $admin): void
    {
        $this->store[$keyname] = new Extension($keyname, false, $admin, '');
    }

    public function remove(string $keyname): void
    {
        unset($this->store[$keyname]);
    }
}

class ExtensionManagementTest extends TestCase
{
    private InMemoryExtensionRepository $repo;

    private ExtensionCatalog $catalog;

    protected function setUp(): void
    {
        $this->repo = new InMemoryExtensionRepository();
        // scan the real extension folders of the project
        $this->catalog = new ExtensionCatalog(dirname(__DIR__, 4) . '/');
    }

    public function testCatalogFindsKnownExtensionsOnDisk(): void
    {
        $available = $this->catalog->available();

        $this->assertArrayHasKey('ckeditor', $available);
        $this->assertArrayHasKey('admin_config', $available);
        $this->assertTrue($available['admin_config']['admin'], 'sysext extensions are admin');
        $this->assertFalse($available['ckeditor']['admin']);
    }

    public function testListMergesRegistryAndDisk(): void
    {
        $this->repo->register('ckeditor', false);
        $this->repo->setActive('ckeditor', true);

        $items = (new ListExtensions($this->repo, $this->catalog))->execute();
        $byKey = array_column($items, null, 'keyname');

        $this->assertTrue($byKey['ckeditor']['installed']);
        $this->assertTrue($byKey['ckeditor']['active']);
        $this->assertTrue($byKey['ckeditor']['onDisk']);
        // an on-disk but unregistered extension shows as not installed
        $this->assertFalse($byKey['libsecure']['installed']);
        $this->assertTrue($byKey['libsecure']['onDisk']);
    }

    public function testInstallRegistersDiskExtension(): void
    {
        $ok = (new InstallExtension($this->repo, $this->catalog))->execute('ckeditor');

        $this->assertTrue($ok);
        $this->assertNotNull($this->repo->find('ckeditor'));
        $this->assertFalse($this->repo->find('ckeditor')->active, 'installed inactive by default');
    }

    public function testInstallUnknownExtensionFails(): void
    {
        $this->assertFalse((new InstallExtension($this->repo, $this->catalog))->execute('does-not-exist'));
    }

    public function testInstallAlreadyInstalledFails(): void
    {
        $this->repo->register('ckeditor', false);

        $this->assertFalse((new InstallExtension($this->repo, $this->catalog))->execute('ckeditor'));
    }

    public function testEnableDisable(): void
    {
        $this->repo->register('ckeditor', false);
        $service = new SetExtensionActive($this->repo);

        $this->assertTrue($service->execute('ckeditor', true));
        $this->assertTrue($this->repo->find('ckeditor')->active);

        $service->execute('ckeditor', false);
        $this->assertFalse($this->repo->find('ckeditor')->active);
    }

    public function testEnableUnknownFails(): void
    {
        $this->assertFalse((new SetExtensionActive($this->repo))->execute('nope', true));
    }

    public function testUninstallRemovesFromRegistry(): void
    {
        $this->repo->register('ckeditor', false);

        $this->assertTrue((new UninstallExtension($this->repo))->execute('ckeditor'));
        $this->assertNull($this->repo->find('ckeditor'));
    }
}
