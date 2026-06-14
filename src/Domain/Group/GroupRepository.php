<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\Group;

interface GroupRepository
{
    /**
     * @return Group[] ordered by name
     */
    public function findAll(): array;

    public function findById(int $id): ?Group;

    /**
     * @return int id of the new group
     */
    public function add(string $name): int;

    public function rename(int $id, string $name): void;

    /**
     * removes the group including the user memberships
     */
    public function delete(int $id): void;
}
