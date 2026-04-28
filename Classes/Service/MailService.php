<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;

class MailService
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    public function queue(string $recipient, string $subject, string $body, ?\DateTimeImmutable $scheduledAt = null): void
    {
        $this->connectionPool
            ->getConnectionForTable('tx_maimail_queue')
            ->insert('tx_maimail_queue', [
                'recipient' => $recipient,
                'subject' => $subject,
                'body' => $body,
                'status' => 'pending',
                'scheduled_at' => ($scheduledAt ?? new \DateTimeImmutable())->getTimestamp(),
                'retry_count' => 0,
            ]);
    }
}
