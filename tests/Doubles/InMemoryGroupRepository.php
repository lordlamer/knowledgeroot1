<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Doubles;

use Knowledgeroot\Domain\Group\Group;
use Knowledgeroot\Domain\Group\GroupRepository;

class InMemoryGroupRepository implements GroupRepository
{
    /** @var array<int, Group> */
    public array $groups = [];

    private int $nextId = 1;

    /**
     * @param Group[] $groups
     */
    public function __construct(array $groups = [])
    {
        foreach ($groups as $group) {
            $this->groups[$group->id] = $group;
            $this->nextId = max($this->nextId, $group->id + 1);
        }
    }

    public function findAll(): array
    {
        $all = array_values($this->groups);
        usort($all, fn (Group $a, Group $b) => strcmp($a->name, $b->name));

        return $all;
    }

    public function findById(int $id): ?Group
    {
        return $this->groups[$id] ?? null;
    }

    public function add(string $name): int
    {
        $id = $this->nextId++;
        $this->groups[$id] = new Group($id, $name, true);

        return $id;
    }

    public function rename(int $id, string $name): void
    {
        $this->groups[$id] = new Group($id, $name, $this->groups[$id]->enabled);
    }

    public function delete(int $id): void
    {
        unset($this->groups[$id]);
    }
}
