<?php
declare(strict_types=1);

namespace Maispace\MaiMail\Domain\Model;

final class MailLog
{
    public function __construct(
        private readonly int $uid,
        private readonly string $subject,
        private readonly string $recipient,
        private readonly string $status,
        private readonly int $sentAt,
        private readonly string $errorMessage,
    ) {
    }

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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSentAt(): int
    {
        return $this->sentAt;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
