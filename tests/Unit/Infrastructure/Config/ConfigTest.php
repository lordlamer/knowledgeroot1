<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Infrastructure\Config;

use Knowledgeroot\Infrastructure\Config\Config;
use Knowledgeroot\Infrastructure\Config\IniWriter;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private string $iniFile;

    protected function setUp(): void
    {
        $this->iniFile = tempnam(sys_get_temp_dir(), 'krcfg');

        file_put_contents($this->iniFile, <<<INI
            [base]
            title = Knowledgeroot
            locale = en_US

            [db]
            adapter = "pdo_sqlite"
            params.host = "localhost"
            params.dbname = "data/knowledgeroot.sqlite"

            [development]
            sqldebug = false
            runtime = true
            INI);
    }

    protected function tearDown(): void
    {
        @unlink($this->iniFile);
    }

    public function testSectionsAreAccessibleAsObjects(): void
    {
        $config = Config::fromIniFile($this->iniFile);

        $this->assertSame('Knowledgeroot', $config->base->title);
        $this->assertSame('pdo_sqlite', $config->db->adapter);
    }

    public function testDottedKeysBecomeNested(): void
    {
        $config = Config::fromIniFile($this->iniFile);

        $this->assertSame('localhost', $config->db->params->host);
        $this->assertSame('data/knowledgeroot.sqlite', $config->db->params->dbname);
    }

    public function testBooleansBehaveLikeLegacyZendConfig(): void
    {
        $config = Config::fromIniFile($this->iniFile);

        // parse_ini_file maps false to "" and true to "1"
        $this->assertEmpty($config->development->sqldebug);
        $this->assertNotEmpty($config->development->runtime);
    }

    public function testMissingKeysReturnNull(): void
    {
        $config = Config::fromIniFile($this->iniFile);

        $this->assertNull($config->doesnotexist);
        $this->assertNull($config->base->doesnotexist);
    }

    public function testValuesCanBeModified(): void
    {
        $config = Config::fromIniFile($this->iniFile);
        $config->base->title = 'Changed';

        $this->assertSame('Changed', $config->base->title);
        $this->assertSame('Changed', $config->toArray()['base']['title']);
    }

    public function testWriterRoundTrip(): void
    {
        $config = Config::fromIniFile($this->iniFile);

        $target = tempnam(sys_get_temp_dir(), 'krcfg');
        (new IniWriter())->write($target, $config);
        $reloaded = Config::fromIniFile($target);
        @unlink($target);

        $this->assertSame($config->toArray(), $reloaded->toArray());
    }
}
