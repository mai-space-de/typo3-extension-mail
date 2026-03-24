<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Domain model representing a queued mail message.
 */
class MailQueue extends AbstractEntity
{
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_RETRY = 'retry';

    protected string $sender = '';

    protected string $recipients = '';

    protected string $subject = '';

    protected string $body = '';

    protected string $attachments = '';

    protected string $status = self::STATUS_QUEUED;

    protected int $priority = 0;

    protected ?\DateTimeInterface $scheduledAt = null;

    protected ?\DateTimeInterface $sentAt = null;

    protected string $errorMessage = '';

    protected int $retryCount = 0;

    protected ?\DateTimeInterface $createdAt = null;

    protected ?\DateTimeInterface $updatedAt = null;

    public function getSender(): string
    {
        return $this->sender;
    }

    public function setSender(string $sender): void
    {
        $this->sender = $sender;
    }

    public function getRecipients(): string
    {
        return $this->recipients;
    }

    public function setRecipients(string $recipients): void
    {
        $this->recipients = $recipients;
    }

    /**
     * Returns recipients as an array of email addresses.
     *
     * @return array<string, string>
     */
    public function getRecipientsArray(): array
    {
        if (empty($this->recipients)) {
            return [];
        }
        $decoded = json_decode($this->recipients, true);
        return \is_array($decoded) ? $decoded : [];
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getAttachments(): string
    {
        return $this->attachments;
    }

    public function setAttachments(string $attachments): void
    {
        $this->attachments = $attachments;
    }

    /**
     * Returns attachments as an array of file paths.
     *
     * @return string[]
     */
    public function getAttachmentsArray(): array
    {
        if (empty($this->attachments)) {
            return [];
        }
        $decoded = json_decode($this->attachments, true);
        return \is_array($decoded) ? $decoded : [];
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getScheduledAt(): ?\DateTimeInterface
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(?\DateTimeInterface $scheduledAt): void
    {
        $this->scheduledAt = $scheduledAt;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeInterface $sentAt): void
    {
        $this->sentAt = $sentAt;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    public function setRetryCount(int $retryCount): void
    {
        $this->retryCount = $retryCount;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function isQueued(): bool
    {
        return $this->status === self::STATUS_QUEUED;
    }

    public function isSending(): bool
    {
        return $this->status === self::STATUS_SENDING;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isRetry(): bool
    {
        return $this->status === self::STATUS_RETRY;
    }

    public function isDue(): bool
    {
        if ($this->scheduledAt === null) {
            return true;
        }
        return $this->scheduledAt <= new \DateTimeImmutable();
    }
}
