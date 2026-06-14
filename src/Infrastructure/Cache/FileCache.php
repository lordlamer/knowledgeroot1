<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Cache;

/**
 * Lightweight file cache, replacement for the previously used
 * Zend_Cache Core/File combination. Implements the same API surface
 * the legacy code relies on: load(), save(), test().
 *
 * When caching is disabled, load()/test() always miss and save() is a
 * no-op - same behaviour as Zend_Cache with caching = false.
 */
class FileCache
{
    public function __construct(
        private readonly string $directory,
        private readonly bool $enabled = true,
        private readonly int $lifetime = 7200,
    ) {
    }

    /**
     * @return mixed false on cache miss
     */
    public function load(string $id): mixed
    {
        if (!$this->enabled) {
            return false;
        }

        $file = $this->file($id);
        if (!is_file($file)) {
            return false;
        }

        if ($this->lifetime > 0 && filemtime($file) + $this->lifetime < time()) {
            @unlink($file);
            return false;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return false;
        }

        return unserialize($content);
    }

    public function save(mixed $data, string $id, array $tags = []): bool
    {
        if (!$this->enabled) {
            return true;
        }

        return file_put_contents($this->file($id), serialize($data), LOCK_EX) !== false;
    }

    public function test(string $id): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $file = $this->file($id);

        if (!is_file($file)) {
            return false;
        }

        if ($this->lifetime > 0 && filemtime($file) + $this->lifetime < time()) {
            @unlink($file);
            return false;
        }

        return true;
    }

    public function remove(string $id): bool
    {
        $file = $this->file($id);

        return !is_file($file) || unlink($file);
    }

    public function clean(): bool
    {
        foreach (glob(rtrim($this->directory, '/\\') . '/kr_cache_*.dat') ?: [] as $file) {
            @unlink($file);
        }

        return true;
    }

    private function file(string $id): string
    {
        return rtrim($this->directory, '/\\') . '/kr_cache_' . md5($id) . '.dat';
    }
}
