<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Application\UserManagement;

use Knowledgeroot\Application\UserManagement\CreateUser;
use Knowledgeroot\Application\UserManagement\DeleteUser;
use Knowledgeroot\Application\UserManagement\InvalidUserData;
use Knowledgeroot\Application\UserManagement\UpdateUser;
use Knowledgeroot\Application\UserManagement\UserFormData;
use Knowledgeroot\Domain\User\User;
use Knowledgeroot\Tests\Doubles\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

class UserCrudTest extends TestCase
{
    private InMemoryUserRepository $users;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository([
            new User(
                id: 1,
                name: 'admin',
                passwordHash: md5('admin'),
                defaultGroup: 2,
                defaultRights: 210,
                admin: true,
                rightEdit: true,
                enabled: true,
                treeCache: 'a:0:{}',
                theme: 'green',
                language: 'de_DE',
            ),
        ]);
    }

    private function formData(string $name = 'newuser', string $password = 'secret'): UserFormData
    {
        return new UserFormData(
            name: $name,
            password: $password,
            theme: 'green',
            enabled: true,
            defaultGroup: 2,
            admin: false,
            rightEdit: true,
            userRights: 2,
            groupRights: 1,
            otherRights: 0,
            groupIds: [2, 3],
        );
    }

    public function testCreateUserStoresModernPasswordHashAndGroups(): void
    {
        $id = (new CreateUser($this->users))->execute($this->formData());

        $created = $this->users->findById($id);
        $this->assertSame('newuser', $created->name);
        $this->assertTrue(password_verify('secret', $created->passwordHash));
        $this->assertSame(210, $created->defaultRights);
        $this->assertSame([2, 3], $this->users->groupIdsOfUser($id));
    }

    public function testCreateUserRejectsEmptyName(): void
    {
        $this->expectException(InvalidUserData::class);
        (new CreateUser($this->users))->execute($this->formData(name: '  '));
    }

    public function testCreateUserRejectsEmptyPassword(): void
    {
        $this->expectException(InvalidUserData::class);
        (new CreateUser($this->users))->execute($this->formData(password: ''));
    }

    public function testCreateUserRejectsDuplicateName(): void
    {
        $this->expectException(InvalidUserData::class);
        (new CreateUser($this->users))->execute($this->formData(name: 'admin'));
    }

    public function testUpdateUserKeepsPasswordWhenEmpty(): void
    {
        (new UpdateUser($this->users))->execute(1, $this->formData(name: 'admin2', password: ''));

        $updated = $this->users->findById(1);
        $this->assertSame('admin2', $updated->name);
        $this->assertSame(md5('admin'), $updated->passwordHash);
        // fields without form input stay untouched
        $this->assertSame('a:0:{}', $updated->treeCache);
        $this->assertSame('de_DE', $updated->language);
    }

    public function testUpdateUserReplacesPasswordWhenGiven(): void
    {
        (new UpdateUser($this->users))->execute(1, $this->formData(name: 'admin', password: 'newpass'));

        $this->assertTrue(password_verify('newpass', $this->users->findById(1)->passwordHash));
    }

    public function testUpdateUserRejectsRenameToExistingName(): void
    {
        (new CreateUser($this->users))->execute($this->formData(name: 'other'));

        $this->expectException(InvalidUserData::class);
        (new UpdateUser($this->users))->execute(1, $this->formData(name: 'other', password: ''));
    }

    public function testUpdateUnknownUserFails(): void
    {
        $this->expectException(InvalidUserData::class);
        (new UpdateUser($this->users))->execute(999, $this->formData());
    }

    public function testDeleteUserRemovesUser(): void
    {
        $id = (new CreateUser($this->users))->execute($this->formData());

        $this->assertTrue((new DeleteUser($this->users))->execute($id, currentUserId: 1));
        $this->assertNull($this->users->findById($id));
    }

    public function testDeleteOwnAccountIsRejected(): void
    {
        $this->assertFalse((new DeleteUser($this->users))->execute(1, currentUserId: 1));
        $this->assertNotNull($this->users->findById(1));
    }

    public function testDeleteUnknownUserFails(): void
    {
        $this->assertFalse((new DeleteUser($this->users))->execute(999, currentUserId: 1));
    }
}
