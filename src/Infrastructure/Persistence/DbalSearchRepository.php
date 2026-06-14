<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Knowledgeroot\Domain\Search\SearchQuery;
use Knowledgeroot\Domain\Search\SearchRepository;

/**
 * Search queries over content, tree and files. The like conditions
 * match the raw word as well as its htmlentities() form because the
 * legacy editor stores entity encoded content.
 */
class DbalSearchRepository implements SearchRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function findContentPageId(int $contentId): ?int
    {
        $pageId = $this->connection->fetchOne('SELECT belongs_to FROM content WHERE id = ?', [$contentId]);

        return $pageId === false ? null : (int) $pageId;
    }

    public function searchContent(SearchQuery $query, int $userId, int $defaultGroupId, array $groupIds, bool $isAdmin): array
    {
        [$where, $params] = $this->buildWhere($query, ['c.content', 'c.title']);
        if ($where === '') {
            return [];
        }

        if ($isAdmin) {
            $sql = "SELECT c.id, c.belongs_to, c.title FROM content c WHERE ($where) AND c.deleted = 0";
        } else {
            $groupCol = 'c.' . $this->connection->quoteIdentifier('group');

            $rightsClause = "(c.otherrights > 0) OR ($groupCol = ? AND c.grouprights > 0)";
            $rightsParams = [$defaultGroupId];

            foreach ($groupIds as $groupId) {
                $rightsClause .= " OR ($groupCol = ? AND c.grouprights > 0)";
                $rightsClause .= " OR (a.owner_group_id = ? AND a.owner_group = 'g' AND a.rights > 0)";
                $rightsParams[] = $groupId;
                $rightsParams[] = $groupId;
            }

            $rightsClause .= " OR (a.owner_group_id = ? AND a.owner_group = 'o' AND a.rights > 0)";
            $rightsClause .= ' OR (c.owner = ? AND c.userrights > 0)';
            $rightsParams[] = $userId;
            $rightsParams[] = $userId;

            $sql = "SELECT DISTINCT c.id, c.belongs_to, c.title FROM content c
                LEFT JOIN access a ON a.belongs_to = c.id AND a.table_name = 'content'
                WHERE ($where) AND c.deleted = 0 AND ($rightsClause)";
            $params = array_merge($params, $rightsParams);
        }

        $rows = $this->connection->fetchAllAssociative($sql, $params);

        return array_map(static fn (array $row) => [
            'id' => (int) $row['id'],
            'pageId' => (int) $row['belongs_to'],
            'title' => (string) $row['title'],
        ], $rows);
    }

    public function searchPages(SearchQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, ['title']);
        if ($where === '') {
            return [];
        }

        $rows = $this->connection->fetchAllAssociative(
            "SELECT id, title FROM tree WHERE ($where) AND deleted = 0",
            $params
        );

        return array_map(static fn (array $row) => [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
        ], $rows);
    }

    public function searchFiles(SearchQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, ['f.filename']);
        if ($where === '') {
            return [];
        }

        $rows = $this->connection->fetchAllAssociative(
            "SELECT f.id, t.id AS tid, f.filename, f.filesize, f.date
             FROM files f
             JOIN content c ON f.belongs_to = c.id
             JOIN tree t ON c.belongs_to = t.id
             WHERE ($where) AND f.deleted = 0",
            $params
        );

        return array_map(static fn (array $row) => [
            'id' => (int) $row['id'],
            'pageId' => (int) $row['tid'],
            'filename' => (string) $row['filename'],
            'filesize' => (int) $row['filesize'],
            'date' => (string) $row['date'],
        ], $rows);
    }

    /**
     * @param string[] $fields
     * @return array{0: string, 1: list<mixed>}
     */
    private function buildWhere(SearchQuery $query, array $fields): array
    {
        $like = $this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform ? 'ILIKE' : 'LIKE';

        $parts = [];
        $params = [];

        foreach ($query->and as $word) {
            $parts[] = '(' . $this->wordCondition($word, $fields, $like, 'OR', $params) . ')';
        }

        foreach ($query->not as $word) {
            $parts[] = '(' . $this->wordCondition($word, $fields, 'NOT ' . $like, 'AND', $params) . ')';
        }

        foreach ($query->orGroups as $group) {
            $groupParts = [];
            foreach ($group as $word) {
                $groupParts[] = '(' . $this->wordCondition($word, $fields, $like, 'OR', $params) . ')';
            }
            if ($groupParts !== []) {
                $parts[] = '(' . implode(' OR ', $groupParts) . ')';
            }
        }

        return [implode(' AND ', $parts), $params];
    }

    /**
     * @param string[] $fields
     * @param list<mixed> $params
     */
    private function wordCondition(string $word, array $fields, string $like, string $glue, array &$params): string
    {
        $variants = array_unique([$word, htmlentities($word, ENT_NOQUOTES, 'UTF-8')]);

        $conditions = [];
        foreach ($fields as $field) {
            foreach ($variants as $variant) {
                $conditions[] = "$field $like ?";
                $params[] = '%' . $variant . '%';
            }
        }

        return implode(' ' . $glue . ' ', $conditions);
    }
}
