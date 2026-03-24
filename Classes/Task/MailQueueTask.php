<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Task;

use Maispace\MaiMail\Service\MailQueueService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Scheduler task to process the mail queue.
 */
class MailQueueTask extends AbstractTask
{
    /**
     * Number of mails to send per execution (0 = unlimited).
     */
    public int $batchSize = 0;

    public function execute(): bool
    {
        /** @var MailQueueService $service */
        $service = GeneralUtility::makeInstance(MailQueueService::class);

        if ($this->batchSize > 0) {
            $sent = $service->sendBatch($this->batchSize);
        } else {
            $sent = $service->sendAll();
        }

        $this->logger?->info('MailQueueTask: sent {count} mails.', ['count' => $sent]);

        return true;
    }
}
