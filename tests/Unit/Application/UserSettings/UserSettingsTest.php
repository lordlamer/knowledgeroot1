<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Application\UserSettings;

use Knowledgeroot\Application\UserManagement\InvalidUserData;
use Knowledgeroot\Application\UserSettings\ChangeOwnPassword;
use Knowledgeroot\Application\UserSettings\UpdateOwnPreferences;
use Knowledgeroot\Domain\User\User;
use Knowledgeroot\Tests\Doubles\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

class UserSettingsTest extends TestCase
{
    private InMemoryUserRepository $users;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository([
            new User(1, 'maria', md5('old'), 2, 210, false, true, true, '', 'green', 'en_US.UTF8'),
        ]);
    }

    public function testChangePasswordStoresModernHash(): void
    {
        (new ChangeOwnPassword($this->users))->execute(1, 'newpass', 'newpass');

        $this->assertTrue(password_verify('newpass', $this->users->findById(1)->passwordHash));
    }

    public function testChangePasswordRejectsMismatch(): void
    {
        $this->expectException(InvalidUserData::class);
        (new ChangeOwnPassword($this->users))->execute(1, 'newpass', 'other');
    }

    public function testChangePasswordRejectsEmptyPassword(): void
    {
        $this->expectException(InvalidUserData::class);
        (new ChangeOwnPassword($this->users))->execute(1, '', '');
    }

    public function testChangePasswordRejectsGuest(): void
    {
        $this->expectException(InvalidUserData::class);
        (new ChangeOwnPassword($this->users))->execute(0, 'newpass', 'newpass');
    }

    public function testUpdatePreferences(): void
    {
        (new UpdateOwnPreferences($this->users))->execute(1, 'wordpress', 'de_DE.UTF8');

        $user = $this->users->findById(1);
        $this->assertSame('wordpress', $user->theme);
        $this->assertSame('de_DE.UTF8', $user->language);
    }

    public function testUpdatePreferencesRejectsUnknownUser(): void
    {
        $this->expectException(InvalidUserData::class);
        (new UpdateOwnPreferences($this->users))->execute(99, 'green', 'en_US.UTF8');
    }
}
