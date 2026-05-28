<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Domain\Model;

final class MailQueue
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly int $uid,
        private readonly string $subject,
        private readonly string $recipient,
        private readonly string $body,
        private readonly string $status,
        private readonly int $retryCount,
        private readonly int $scheduledAt,
        private readonly int $sentAt,
        private readonly array $headers = [],
    ) {}

    public function getUid(): int
    {
        return $this->uid;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    public function getScheduledAt(): int
    {
        return $this->scheduledAt;
    }

    public function getSentAt(): int
    {
        return $this->sentAt;
    }
}
