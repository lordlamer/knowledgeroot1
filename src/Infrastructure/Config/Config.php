<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Config;

/**
 * Lightweight replacement for Zend_Config / Zend_Config_Ini.
 *
 * Provides nested object access (e.g. $config->db->params->host) over an
 * ini file with sections. Keys containing dots are expanded to nested
 * structures, matching the old Zend_Config_Ini behaviour.
 */
class Config implements \IteratorAggregate, \Countable
{
    /** @var array<string, mixed> */
    private array $data = [];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = is_array($value) ? new self($value) : $value;
        }
    }

    /**
     * Read an ini file with sections into a Config instance.
     * Dotted keys (params.host) become nested values.
     */
    public static function fromIniFile(string $file): self
    {
        if (!is_file($file)) {
            throw new \RuntimeException('Config file not found: ' . $file);
        }

        $sections = parse_ini_file($file, true, INI_SCANNER_NORMAL);
        if ($sections === false) {
            throw new \RuntimeException('Could not parse config file: ' . $file);
        }

        $data = [];
        foreach ($sections as $section => $values) {
            if (!is_array($values)) {
                $data[$section] = $values;
                continue;
            }

            $data[$section] = [];
            foreach ($values as $key => $value) {
                self::setNested($data[$section], explode('.', (string) $key), $value);
            }
        }

        return new self($data);
    }

    /**
     * @param array<string, mixed> $target
     * @param string[] $path
     */
    private static function setNested(array &$target, array $path, mixed $value): void
    {
        $key = array_shift($path);
        if ($path === []) {
            $target[$key] = $value;
            return;
        }

        if (!isset($target[$key]) || !is_array($target[$key])) {
            $target[$key] = [];
        }

        self::setNested($target[$key], $path, $value);
    }

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->data[$name] = is_array($value) ? new self($value) : $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function __unset(string $name): void
    {
        unset($this->data[$name]);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->data[$name] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->data as $key => $value) {
            $result[$key] = $value instanceof self ? $value->toArray() : $value;
        }

        return $result;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }
}
