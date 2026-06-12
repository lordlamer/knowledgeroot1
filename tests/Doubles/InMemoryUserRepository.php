<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Doubles;

use Knowledgeroot\Domain\User\User;
use Knowledgeroot\Domain\User\UserRepository;

class InMemoryUserRepository implements UserRepository
{
    /** @var array<int, User> */
    public array $users = [];

    /** @var array<int, int[]> user id => group ids */
    public array $memberships = [];

    private int $nextId = 1;

    /**
     * @param User[] $users
     */
    public function __construct(array $users = [])
    {
        foreach ($users as $user) {
            $this->users[$user->id] = $user;
            $this->nextId = max($this->nextId, $user->id + 1);
        }
    }

    public function findEnabledByName(string $name): ?User
    {
        foreach ($this->users as $user) {
            if ($user->name === $name && $user->enabled) {
                return $user;
            }
        }

        return null;
    }

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function findAll(): array
    {
        $all = array_values($this->users);
        usort($all, fn (User $a, User $b) => strcmp($a->name, $b->name));

        return $all;
    }

    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        foreach ($this->users as $user) {
            if ($user->name === $name && $user->id !== $excludeId) {
                return true;
            }
        }

        return false;
    }

    public function add(User $user, array $groupIds): int
    {
        $id = $this->nextId++;

        $this->users[$id] = new User(
            id: $id,
            name: $user->name,
            passwordHash: $user->passwordHash,
            defaultGroup: $user->defaultGroup,
            defaultRights: $user->defaultRights,
            admin: $user->admin,
            rightEdit: $user->rightEdit,
            enabled: $user->enabled,
            treeCache: $user->treeCache,
            theme: $user->theme,
            language: $user->language,
        );
        $this->memberships[$id] = $groupIds;

        return $id;
    }

    public function update(User $user, array $groupIds): void
    {
        $this->users[$user->id] = $user;
        $this->memberships[$user->id] = $groupIds;
    }

    public function delete(int $id): void
    {
        unset($this->users[$id], $this->memberships[$id]);
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $u = $this->users[$id];
        $this->users[$id] = new User($u->id, $u->name, $passwordHash, $u->defaultGroup, $u->defaultRights, $u->admin, $u->rightEdit, $u->enabled, $u->treeCache, $u->theme, $u->language);
    }

    public function updatePreferences(int $id, string $theme, string $language): void
    {
        $u = $this->users[$id];
        $this->users[$id] = new User($u->id, $u->name, $u->passwordHash, $u->defaultGroup, $u->defaultRights, $u->admin, $u->rightEdit, $u->enabled, $u->treeCache, $theme, $language);
    }

    public function groupIdsOfUser(int $id): array
    {
        return $this->memberships[$id] ?? [];
    }

    public function countByDefaultGroup(int $groupId): int
    {
        return count(array_filter($this->users, fn (User $u) => $u->defaultGroup === $groupId));
    }
}
