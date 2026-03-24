<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Event;

use Maispace\MaiMail\Domain\Model\MailQueue;

/**
 * Dispatched when sending a queued mail fails.
 */
final class MailFailedEvent
{
    public function __construct(
        private readonly MailQueue $mailQueue,
        private readonly \Throwable $exception
    ) {
    }

    public function getMailQueue(): MailQueue
    {
        return $this->mailQueue;
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }
}
