<?php

declare(strict_types=1);

namespace Knowledgeroot\Tests\Unit\Application\Login;

use Knowledgeroot\Application\Login\AuthenticateUser;
use Knowledgeroot\Application\Login\AuthenticationFailure;
use Knowledgeroot\Domain\User\LoginAttempt;
use Knowledgeroot\Domain\User\User;
use Knowledgeroot\Tests\Doubles\InMemoryThrottleRepository;
use Knowledgeroot\Tests\Doubles\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

class AuthenticateUserTest extends TestCase
{
    private const DELAY = 30;
    private const MAX = 5;

    private InMemoryThrottleRepository $throttle;

    protected function setUp(): void
    {
        $this->throttle = new InMemoryThrottleRepository();
    }

    private function makeUser(string $passwordHash, bool $enabled = true): User
    {
        return new User(
            id: 1,
            name: 'admin',
            passwordHash: $passwordHash,
            defaultGroup: 2,
            defaultRights: 210,
            admin: true,
            rightEdit: true,
            enabled: $enabled,
            treeCache: '',
            theme: '',
            language: 'de_DE',
        );
    }

    private function service(?User $user): AuthenticateUser
    {
        $users = new InMemoryUserRepository($user === null ? [] : [$user]);

        return new AuthenticateUser($users, $this->throttle, self::DELAY, self::MAX);
    }

    public function testUnknownUserFails(): void
    {
        $result = $this->service(null)->authenticate('nobody', 'secret');

        $this->assertFalse($result->isSuccess());
        $this->assertSame(AuthenticationFailure::InvalidCredentials, $result->failure);
        $this->assertSame([], $this->throttle->attempts);
    }

    public function testDisabledUserFails(): void
    {
        $result = $this->service($this->makeUser(md5('secret'), enabled: false))
            ->authenticate('admin', 'secret');

        $this->assertSame(AuthenticationFailure::InvalidCredentials, $result->failure);
    }

    public function testWrongPasswordFailsAndIsRecorded(): void
    {
        $result = $this->service($this->makeUser(md5('secret')))->authenticate('admin', 'wrong');

        $this->assertSame(AuthenticationFailure::InvalidCredentials, $result->failure);
        $this->assertSame(1, $this->throttle->attempts[1]->trials);
    }

    public function testLegacyMd5PasswordSucceeds(): void
    {
        $result = $this->service($this->makeUser(md5('secret')))->authenticate('admin', 'secret');

        $this->assertTrue($result->isSuccess());
        $this->assertSame('admin', $result->user->name);
    }

    public function testLegacyPasswordWithQuotesUsesAddslashes(): void
    {
        // the legacy code hashed md5(addslashes($password))
        $password = "pa'ss\"word";
        $result = $this->service($this->makeUser(md5(addslashes($password))))
            ->authenticate('admin', $password);

        $this->assertTrue($result->isSuccess());
    }

    public function testModernPasswordHashSucceeds(): void
    {
        $hash = password_hash('secret', PASSWORD_DEFAULT);
        $result = $this->service($this->makeUser($hash))->authenticate('admin', 'secret');

        $this->assertTrue($result->isSuccess());
    }

    public function testRecentFailureTriggersRetryDelay(): void
    {
        $this->throttle->attempts[1] = new LoginAttempt(1, 1, time());

        $result = $this->service($this->makeUser(md5('secret')))->authenticate('admin', 'secret');

        $this->assertSame(AuthenticationFailure::RetryDelay, $result->failure);
    }

    public function testTooManyFailuresBlocksAccount(): void
    {
        $this->throttle->attempts[1] = new LoginAttempt(1, self::MAX, time() - self::DELAY - 1);

        $result = $this->service($this->makeUser(md5('secret')))->authenticate('admin', 'secret');

        $this->assertSame(AuthenticationFailure::Blocked, $result->failure);
    }

    public function testOldFailuresDoNotBlockLogin(): void
    {
        $this->throttle->attempts[1] = new LoginAttempt(1, 2, time() - self::DELAY - 1);

        $result = $this->service($this->makeUser(md5('secret')))->authenticate('admin', 'secret');

        $this->assertTrue($result->isSuccess());
    }

    public function testSuccessResetsThrottleAndPurgesOldEntries(): void
    {
        $this->throttle->attempts[1] = new LoginAttempt(1, 2, time() - self::DELAY - 1);

        $this->service($this->makeUser(md5('secret')))->authenticate('admin', 'secret');

        $this->assertSame(0, $this->throttle->attempts[1]->trials);
        $this->assertNotNull($this->throttle->purgedBefore);
        $this->assertLessThan(time(), $this->throttle->purgedBefore);
    }
}
