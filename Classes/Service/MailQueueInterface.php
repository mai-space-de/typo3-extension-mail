<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Service;

interface MailQueueInterface
{
    /**
     * @param array<string, string> $headers Additional email headers (e.g. List-Unsubscribe)
     */
    public function queue(string $recipient, string $subject, string $htmlBody, ?\DateTimeInterface $scheduledAt = null, array $headers = []): void;
}
