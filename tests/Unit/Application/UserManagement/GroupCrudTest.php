<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Application\UserManagement;

use Knowledgeroot\Application\UserManagement\CreateGroup;
use Knowledgeroot\Application\UserManagement\DeleteGroup;
use Knowledgeroot\Application\UserManagement\InvalidUserData;
use Knowledgeroot\Application\UserManagement\UpdateGroup;
use Knowledgeroot\Domain\Group\Group;
use Knowledgeroot\Domain\User\User;
use Knowledgeroot\Tests\Doubles\InMemoryGroupRepository;
use Knowledgeroot\Tests\Doubles\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

class GroupCrudTest extends TestCase
{
    private InMemoryGroupRepository $groups;

    private InMemoryUserRepository $users;

    protected function setUp(): void
    {
        $this->groups = new InMemoryGroupRepository([
            new Group(1, 'admin', true),
            new Group(2, 'users', true),
        ]);

        $this->users = new InMemoryUserRepository([
            new User(
                id: 1,
                name: 'admin',
                passwordHash: md5('admin'),
                defaultGroup: 1,
                defaultRights: 210,
                admin: true,
                rightEdit: true,
                enabled: true,
                treeCache: '',
                theme: '',
                language: '',
            ),
        ]);
    }

    public function testCreateGroup(): void
    {
        $id = (new CreateGroup($this->groups))->execute('  editors  ');

        $this->assertSame('editors', $this->groups->findById($id)->name);
    }

    public function testCreateGroupRejectsEmptyName(): void
    {
        $this->expectException(InvalidUserData::class);
        (new CreateGroup($this->groups))->execute('   ');
    }

    public function testUpdateGroup(): void
    {
        (new UpdateGroup($this->groups))->execute(2, 'members');

        $this->assertSame('members', $this->groups->findById(2)->name);
    }

    public function testUpdateUnknownGroupFails(): void
    {
        $this->expectException(InvalidUserData::class);
        (new UpdateGroup($this->groups))->execute(999, 'members');
    }

    public function testDeleteGroup(): void
    {
        $this->assertTrue((new DeleteGroup($this->groups, $this->users))->execute(2));
        $this->assertNull($this->groups->findById(2));
    }

    public function testDeleteGroupInUseAsDefaultGroupIsRejected(): void
    {
        // group 1 is the admin user's default group
        $this->assertFalse((new DeleteGroup($this->groups, $this->users))->execute(1));
        $this->assertNotNull($this->groups->findById(1));
    }

    public function testDeleteUnknownGroupFails(): void
    {
        $this->assertFalse((new DeleteGroup($this->groups, $this->users))->execute(999));
    }
}
