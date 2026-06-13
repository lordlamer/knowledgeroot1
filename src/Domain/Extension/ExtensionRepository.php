<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Extension;

interface ExtensionRepository
{
    /**
     * @return Extension[] keyed by keyname
     */
    public function findAll(): array;

    public function find(string $keyname): ?Extension;

    public function setActive(string $keyname, bool $active): void;

    /**
     * register a new extension (installed, inactive by default)
     */
    public function register(string $keyname, bool $admin): void;

    public function remove(string $keyname): void;
}
