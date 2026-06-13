<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Knowledgeroot\Domain\Content\ContentAccess;

/**
 * Port of the legacy content permission model (knowledgeroot::
 * getContentRights / _getContentRights): base rights come from the
 * content row (owner / group / others), additional per-user or
 * per-group rights from the access table; the highest value wins,
 * admins always get 2. Lookups are memoized per request.
 */
class DbalContentAccess implements ContentAccess
{
    /** @var array<int, array<string, mixed>|null> */
    private array $userCache = [];

    /** @var array<int, array<string, mixed>|null> */
    private array $contentCache = [];

    /** @var array<int, int[]> */
    private array $groupCache = [];

    /** @var array<string, int> */
    private array $rightsCache = [];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function rights(int $contentId, int $userId): int
    {
        $cacheKey = $contentId . ':' . $userId;
        if (isset($this->rightsCache[$cacheKey])) {
            return $this->rightsCache[$cacheKey];
        }

        return $this->rightsCache[$cacheKey] = $this->computeRights($contentId, $userId);
    }

    private function computeRights(int $contentId, int $userId): int
    {
        $user = $this->user($userId);

        if ($user === null && $userId !== 0) {
            return 0;
        }

        if ($user !== null && (int) $user['admin'] === 1) {
            return 2;
        }

        $content = $this->content($contentId);
        if ($content === null) {
            return 0;
        }

        $base = $this->baseRights($content, $user, $userId);
        if ($base === 2) {
            return 2;
        }

        return max($base, $this->accessTableRights($contentId, $userId, $user));
    }

    /**
     * @param array<string, mixed> $content
     * @param array<string, mixed>|null $user
     */
    private function baseRights(array $content, ?array $user, int $userId): int
    {
        if ($user !== null && (int) $content['owner'] === (int) $user['id']) {
            return (int) $content['userrights'];
        }

        $contentGroup = (int) $content['group'];

        if ($user !== null && $contentGroup === (int) $user['defaultgroup']) {
            return (int) $content['grouprights'];
        }

        if (in_array($contentGroup, $this->groupsOf($userId), true)) {
            return (int) $content['grouprights'];
        }

        return (int) $content['otherrights'];
    }

    /**
     * @param array<string, mixed>|null $user
     */
    private function accessTableRights(int $contentId, int $userId, ?array $user): int
    {
        $rights = 0;

        $groupIds = $this->groupsOf($userId);
        if ($user !== null) {
            $groupIds[] = (int) $user['defaultgroup'];
        }

        $rows = $this->connection->fetchAllAssociative(
            "SELECT owner_group, owner_group_id, rights FROM access WHERE table_name = 'content' AND belongs_to = ?",
            [$contentId]
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
    private function content(int $contentId): ?array
    {
        if (!array_key_exists($contentId, $this->contentCache)) {
            $row = $this->connection->fetchAssociative(
                'SELECT id, owner, ' . $this->connection->quoteIdentifier('group')
                . ', userrights, grouprights, otherrights FROM content WHERE id = ? AND deleted = 0',
                [$contentId]
            );
            $this->contentCache[$contentId] = $row === false ? null : $row;
        }

        return $this->contentCache[$contentId];
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
