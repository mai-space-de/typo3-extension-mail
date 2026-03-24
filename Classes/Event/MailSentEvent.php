<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Event;

use Maispace\MaiMail\Domain\Model\MailQueue;

/**
 * Dispatched when a queued mail is sent successfully.
 */
final class MailSentEvent
{
    public function __construct(
        private readonly MailQueue $mailQueue
    ) {}

    public function getMailQueue(): MailQueue
    {
        return $this->mailQueue;
    }
}
