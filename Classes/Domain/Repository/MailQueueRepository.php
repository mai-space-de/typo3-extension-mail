<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Domain\Repository;

use Maispace\MaiMail\Domain\Model\MailQueue;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository for MailQueue domain model.
 *
 * @extends Repository<MailQueue>
 */
class MailQueueRepository extends Repository
{
    protected $defaultOrderings = [
        'priority' => QueryInterface::ORDER_DESCENDING,
        'crdate' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * Find all queued mails that are due for sending.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<MailQueue>
     */
    public function findQueued(): \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $now = new \DateTimeImmutable();

        $query->matching(
            $query->logicalAnd(
                $query->logicalOr(
                    $query->equals('status', MailQueue::STATUS_QUEUED),
                    $query->equals('status', MailQueue::STATUS_RETRY)
                ),
                $query->logicalOr(
                    $query->equals('scheduledAt', null),
                    $query->lessThanOrEqual('scheduledAt', $now)
                )
            )
        );

        return $query->execute();
    }

    /**
     * Find mails by a specific status.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<MailQueue>
     */
    public function findByStatus(string $status): \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching($query->equals('status', $status));
        return $query->execute();
    }

    /**
     * Find mails scheduled in the future.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<MailQueue>
     */
    public function findScheduled(): \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $now = new \DateTimeImmutable();

        $query->matching(
            $query->logicalAnd(
                $query->equals('status', MailQueue::STATUS_QUEUED),
                $query->greaterThan('scheduledAt', $now)
            )
        );

        return $query->execute();
    }

    /**
     * Count mails by status using raw query for performance.
     *
     * @return array<string, int>
     */
    public function countByStatus(): array
    {
        $connectionPool = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_maimail_domain_model_mailqueue');

        $rows = $queryBuilder
            ->select('status')
            ->addSelectLiteral('COUNT(*) AS cnt')
            ->from('tx_maimail_domain_model_mailqueue')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, \TYPO3\CMS\Core\Database\Connection::PARAM_INT)),
                    $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, \TYPO3\CMS\Core\Database\Connection::PARAM_INT))
                )
            )
            ->groupBy('status')
            ->executeQuery()
            ->fetchAllAssociative();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['status']] = (int)$row['cnt'];
        }

        return $result;
    }

    /**
     * Find all entries without storage page restriction.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<MailQueue>
     */
    public function findAll(): \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        return $query->execute();
    }

    /**
     * Find queued mails that are due, limited to a given batch size for efficient processing.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<MailQueue>
     */
    public function findQueuedWithLimit(int $limit): \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $now = new \DateTimeImmutable();

        $query->matching(
            $query->logicalAnd(
                $query->logicalOr(
                    $query->equals('status', MailQueue::STATUS_QUEUED),
                    $query->equals('status', MailQueue::STATUS_RETRY)
                ),
                $query->logicalOr(
                    $query->equals('scheduledAt', null),
                    $query->lessThanOrEqual('scheduledAt', $now)
                )
            )
        );

        $query->setLimit($limit);

        return $query->execute();
    }
}
