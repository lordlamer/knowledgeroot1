<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\Page\PageAccess;

/**
 * Port of the legacy permission model (knowledgeroot::getPageRights /
 * checkRecursivPerm): base rights come from the tree row (owner /
 * group / others), additional per-user or per-group rights from the
 * access table; the highest value wins, admins always get 2. canRead()
 * walks up the tree - every ancestor has to be readable.
 *
 * Lookups are memoized for the lifetime of the request.
 */
class DbalPageAccess implements PageAccess
{
    /** @var array<int, array<string, mixed>|null> */
    private array $userCache = [];

    /** @var array<int, array<string, mixed>|null> */
    private array $pageCache = [];

    /** @var array<int, int[]> */
    private array $groupCache = [];

    /** @var array<string, int> */
    private array $rightsCache = [];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function rights(int $pageId, int $userId): int
    {
        $cacheKey = $pageId . ':' . $userId;
        if (isset($this->rightsCache[$cacheKey])) {
            return $this->rightsCache[$cacheKey];
        }

        return $this->rightsCache[$cacheKey] = $this->computeRights($pageId, $userId);
    }

    public function canRead(int $pageId, int $userId): bool
    {
        $page = $this->page($pageId);

        if ($page === null) {
            return false;
        }

        if ($this->rights($pageId, $userId) === 0) {
            return false;
        }

        if ((int) $page['belongs_to'] === 0) {
            return true;
        }

        return $this->canRead((int) $page['belongs_to'], $userId);
    }

    private function computeRights(int $pageId, int $userId): int
    {
        $user = $this->user($userId);

        // unknown (deleted) users get no rights, guests (id 0) are handled
        // like a user without groups
        if ($user === null && $userId !== 0) {
            return 0;
        }

        if ($user !== null && (int) $user['admin'] === 1) {
            return 2;
        }

        $page = $this->page($pageId);
        if ($page === null) {
            return 0;
        }

        $base = $this->baseRights($page, $user, $userId);
        if ($base === 2) {
            return 2;
        }

        return max($base, $this->accessTableRights($pageId, $user, $userId));
    }

    /**
     * @param array<string, mixed> $page
     * @param array<string, mixed>|null $user
     */
    private function baseRights(array $page, ?array $user, int $userId): int
    {
        if ($user !== null && (int) $page['owner'] === (int) $user['id']) {
            return (int) $page['userrights'];
        }

        $pageGroup = (int) $page['group'];

        if ($user !== null && $pageGroup === (int) $user['defaultgroup']) {
            return (int) $page['grouprights'];
        }

        if (in_array($pageGroup, $this->groupsOf($userId), true)) {
            return (int) $page['grouprights'];
        }

        return (int) $page['otherrights'];
    }

    /**
     * @param array<string, mixed>|null $user
     */
    private function accessTableRights(int $pageId, ?array $user, int $userId): int
    {
        $rights = 0;

        $groupIds = $this->groupsOf($userId);
        if ($user !== null) {
            $groupIds[] = (int) $user['defaultgroup'];
        }

        $rows = $this->connection->fetchAllAssociative(
            "SELECT owner_group, owner_group_id, rights FROM access WHERE table_name = 'tree' AND belongs_to = ?",
            [$pageId]
        );

        foreach ($rows as $row) {
            $matches = ($row['owner_group'] === 'o' && (int) $row['owner_group_id'] === $userId)
                || ($row['owner_group'] === 'g' && in_array((int) $row['owner_group_id'], $groupIds, true));

            if ($matches && (int) $row['rights'] > $rights) {
                $rights = (int) $row['rights'];
            }
        }

        return $rights;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function user(int $userId): ?array
    {
        if (!array_key_exists($userId, $this->userCache)) {
            $row = $this->connection->fetchAssociative(
                'SELECT id, admin, defaultgroup FROM users WHERE id = ? AND deleted = 0',
                [$userId]
            );
            $this->userCache[$userId] = $row === false ? null : $row;
        }

        return $this->userCache[$userId];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function page(int $pageId): ?array
    {
        if (!array_key_exists($pageId, $this->pageCache)) {
            $row = $this->connection->fetchAssociative(
                'SELECT id, belongs_to, owner, ' . $this->connection->quoteIdentifier('group')
                . ', userrights, grouprights, otherrights FROM tree WHERE id = ? AND deleted = 0',
                [$pageId]
            );
            $this->pageCache[$pageId] = $row === false ? null : $row;
        }

        return $this->pageCache[$pageId];
    }

    /**
     * @return int[]
     */
    private function groupsOf(int $userId): array
    {
        if (!isset($this->groupCache[$userId])) {
            $ids = $this->connection->fetchFirstColumn('SELECT groupid FROM user_group WHERE userid = ?', [$userId]);
            $this->groupCache[$userId] = array_map(intval(...), $ids);
        }

        return $this->groupCache[$userId];
    }
}
