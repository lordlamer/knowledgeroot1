<?php

declare(strict_types=1);

namespace Knowledgeroot\Domain\User;

interface UserRepository
{
    public function findEnabledByName(string $name): ?User;

    public function findById(int $id): ?User;

    /**
     * @return User[] ordered by name
     */
    public function findAll(): array;

    public function nameExists(string $name, ?int $excludeId = null): bool;

    /**
     * @param int[] $groupIds group memberships (user_group)
     * @return int id of the new user
     */
    public function add(User $user, array $groupIds): int;

    /**
     * @param int[] $groupIds group memberships, replaces the existing ones
     */
    public function update(User $user, array $groupIds): void;

    /**
     * removes the user including the group memberships
     */
    public function delete(int $id): void;

    /**
     * @return int[]
     */
    public function groupIdsOfUser(int $id): array;

    /**
     * how many users have this group as their default group
     */
    public function countByDefaultGroup(int $groupId): int;
}
