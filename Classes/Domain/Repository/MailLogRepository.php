<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Domain\Repository;

use Maispace\MaiMail\Domain\Model\MailLog;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

final class MailLogRepository
{
    private const string TABLE_NAME = 'tx_maimail_log';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    /**
     * @return MailLog[]
     */
    public function findRecent(int $limit): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());

        $rows = $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->orderBy('sent_at', 'DESC')
            ->setMaxResults($limit)
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(static fn(array $row) => self::hydrate($row), $rows);
    }

    private static function hydrate(array $row): MailLog
    {
        return new MailLog(
            uid: (int)$row['uid'],
            subject: (string)$row['subject'],
            recipient: (string)$row['recipient'],
            status: (string)$row['status'],
            sentAt: (int)$row['sent_at'],
            errorMessage: (string)$row['error_message'],
        );
    }
}
