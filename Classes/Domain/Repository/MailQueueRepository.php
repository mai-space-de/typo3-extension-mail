<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Domain\Repository;

use Maispace\MaiMail\Domain\Model\MailQueue;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

final class MailQueueRepository
{
    private const string TABLE_NAME = 'tx_maimail_queue';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    /**
     * @return MailQueue[]
     */
    public function findAll(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());

        $rows = $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->orderBy('crdate', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(static fn(array $row) => self::hydrate($row), $rows);
    }

    /**
     * @return MailQueue[]
     */
    public function findByStatus(string $status): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());

        $rows = $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('status', $queryBuilder->createNamedParameter($status))
            )
            ->orderBy('crdate', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(static fn(array $row) => self::hydrate($row), $rows);
    }

    public function findByUid(int $uid): ?MailQueue
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());

        $row = $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAssociative();

        return $row !== false ? self::hydrate($row) : null;
    }

    private static function hydrate(array $row): MailQueue
    {
        return new MailQueue(
            uid: (int)$row['uid'],
            subject: (string)$row['subject'],
            recipient: (string)$row['recipient'],
            body: (string)$row['body'],
            status: (string)$row['status'],
            retryCount: (int)$row['retry_count'],
            scheduledAt: (int)$row['scheduled_at'],
            sentAt: (int)$row['sent_at'],
        );
    }
}
