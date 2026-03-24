<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Tests\Unit\Domain\Model;

use Maispace\MaiMail\Domain\Model\MailQueue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MailQueue::class)]
final class MailQueueTest extends TestCase
{
    private MailQueue $subject;

    protected function setUp(): void
    {
        $this->subject = new MailQueue();
    }

    #[Test]
    public function defaultStatusIsQueued(): void
    {
        self::assertSame(MailQueue::STATUS_QUEUED, $this->subject->getStatus());
    }

    #[Test]
    public function senderCanBeSetAndRetrieved(): void
    {
        $this->subject->setSender('sender@example.com');
        self::assertSame('sender@example.com', $this->subject->getSender());
    }

    #[Test]
    public function recipientsCanBeSetAndRetrieved(): void
    {
        $recipients = json_encode(['user@example.com' => 'Test User'], JSON_THROW_ON_ERROR);
        $this->subject->setRecipients($recipients);
        self::assertSame($recipients, $this->subject->getRecipients());
    }

    #[Test]
    public function getRecipientsArrayReturnsDecodedArray(): void
    {
        $this->subject->setRecipients('{"user@example.com":"Test User"}');
        self::assertSame(['user@example.com' => 'Test User'], $this->subject->getRecipientsArray());
    }

    #[Test]
    public function getRecipientsArrayReturnsEmptyArrayForEmptyString(): void
    {
        $this->subject->setRecipients('');
        self::assertSame([], $this->subject->getRecipientsArray());
    }

    #[Test]
    public function subjectCanBeSetAndRetrieved(): void
    {
        $this->subject->setSubject('Test Subject');
        self::assertSame('Test Subject', $this->subject->getSubject());
    }

    #[Test]
    public function bodyCanBeSetAndRetrieved(): void
    {
        $this->subject->setBody('<p>Hello World</p>');
        self::assertSame('<p>Hello World</p>', $this->subject->getBody());
    }

    #[Test]
    public function priorityDefaultsToZero(): void
    {
        self::assertSame(0, $this->subject->getPriority());
    }

    #[Test]
    public function priorityCanBeSetAndRetrieved(): void
    {
        $this->subject->setPriority(5);
        self::assertSame(5, $this->subject->getPriority());
    }

    #[Test]
    public function retryCountDefaultsToZero(): void
    {
        self::assertSame(0, $this->subject->getRetryCount());
    }

    #[Test]
    public function retryCountCanBeIncremented(): void
    {
        $this->subject->setRetryCount($this->subject->getRetryCount() + 1);
        self::assertSame(1, $this->subject->getRetryCount());
    }

    #[Test]
    public function statusHelperMethodsReturnCorrectValues(): void
    {
        $this->subject->setStatus(MailQueue::STATUS_QUEUED);
        self::assertTrue($this->subject->isQueued());
        self::assertFalse($this->subject->isSent());
        self::assertFalse($this->subject->isFailed());

        $this->subject->setStatus(MailQueue::STATUS_SENT);
        self::assertTrue($this->subject->isSent());
        self::assertFalse($this->subject->isQueued());

        $this->subject->setStatus(MailQueue::STATUS_FAILED);
        self::assertTrue($this->subject->isFailed());

        $this->subject->setStatus(MailQueue::STATUS_SENDING);
        self::assertTrue($this->subject->isSending());

        $this->subject->setStatus(MailQueue::STATUS_RETRY);
        self::assertTrue($this->subject->isRetry());
    }

    #[Test]
    public function isDueReturnsTrueWhenNoScheduledAt(): void
    {
        $this->subject->setScheduledAt(null);
        self::assertTrue($this->subject->isDue());
    }

    #[Test]
    public function isDueReturnsTrueWhenScheduledAtIsInThePast(): void
    {
        $this->subject->setScheduledAt(new \DateTimeImmutable('-1 hour'));
        self::assertTrue($this->subject->isDue());
    }

    #[Test]
    public function isDueReturnsFalseWhenScheduledAtIsInTheFuture(): void
    {
        $this->subject->setScheduledAt(new \DateTimeImmutable('+1 hour'));
        self::assertFalse($this->subject->isDue());
    }

    #[Test]
    public function getAttachmentsArrayReturnsDecodedArray(): void
    {
        $this->subject->setAttachments('["/path/to/file.pdf","/path/to/image.png"]');
        self::assertSame(['/path/to/file.pdf', '/path/to/image.png'], $this->subject->getAttachmentsArray());
    }

    #[Test]
    public function getAttachmentsArrayReturnsEmptyArrayForEmptyString(): void
    {
        $this->subject->setAttachments('');
        self::assertSame([], $this->subject->getAttachmentsArray());
    }

    #[Test]
    public function scheduledAtCanBeSetAndRetrieved(): void
    {
        $date = new \DateTimeImmutable('2025-06-01 10:00:00');
        $this->subject->setScheduledAt($date);
        self::assertSame($date, $this->subject->getScheduledAt());
    }

    #[Test]
    public function sentAtCanBeSetAndRetrieved(): void
    {
        $date = new \DateTimeImmutable('2025-06-01 10:05:00');
        $this->subject->setSentAt($date);
        self::assertSame($date, $this->subject->getSentAt());
    }

    #[Test]
    public function errorMessageCanBeSetAndRetrieved(): void
    {
        $this->subject->setErrorMessage('Connection refused');
        self::assertSame('Connection refused', $this->subject->getErrorMessage());
    }
}
