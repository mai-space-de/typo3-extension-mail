<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Event;

use Maispace\MaiMail\Domain\Model\MailQueue;

/**
 * Dispatched when a mail is added to the queue.
 */
final class MailQueuedEvent
{
    public function __construct(
        private readonly MailQueue $mailQueue
    ) {}

    public function getMailQueue(): MailQueue
    {
        return $this->mailQueue;
    }
}
