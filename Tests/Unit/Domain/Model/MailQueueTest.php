<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Tests\Unit\Domain\Model;

use Maispace\MaiMail\Domain\Model\MailQueue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MailQueueTest extends TestCase
{
    // ── Constructor / getters ────────────────────────────────────────────────

    #[Test]
    public function getUidReturnsConstructorValue(): void
    {
        $queue = new MailQueue(7, 'Subject', 'to@example.com', '<p>Body</p>', 'queued', 0, 1700000000, 0);
        self::assertSame(7, $queue->getUid());
    }

    #[Test]
    public function getSubjectReturnsConstructorValue(): void
    {
        $queue = new MailQueue(1, 'Newsletter #12', 'to@example.com', '', 'queued', 0, 0, 0);
        self::assertSame('Newsletter #12', $queue->getSubject());
    }

    #[Test]
    public function getRecipientReturnsConstructorValue(): void
    {
        $queue = new MailQueue(1, 'Subject', 'recipient@example.com', '', 'queued', 0, 0, 0);
        self::assertSame('recipient@example.com', $queue->getRecipient());
    }

    #[Test]
    public function getBodyReturnsConstructorValue(): void
    {
        $queue = new MailQueue(1, 'Subject', 'to@example.com', '<h1>Hello</h1>', 'queued', 0, 0, 0);
        self::assertSame('<h1>Hello</h1>', $queue->getBody());
    }

    #[Test]
    public function getStatusReturnsQueuedWhenQueued(): void
    {
        $queue = new MailQueue(1, 'Subject', 'to@example.com', '', 'queued', 0, 0, 0);
        self::assertSame('queued', $queue->getStatus());
    }

    #[Test]
    public function getStatusReturnsSentWhenSent(): void
    {
        $queue = new MailQueue(1, 'Subject', 'to@example.com', '', 'sent', 0, 0, 0);
        self::assertSame('sent', $queue->getStatus());
    }

    #[Test]
    public function getStatusReturnsFailedWhenFailed(): void
    {
        $queue = new MailQueue(1, 'Subject', 'to@example.com', '', 'failed', 2, 0, 0);
        self::assertSame('failed', $queue->getStatus());
    }

    #[Test]
    public function getStatusReturnsDeadWhenDead(): void
    {
        $queue = new MailQueue(1, 'Subject', 'to@example.com', '', 'dead', 3, 0, 0);
        self::assertSame('dead', $queue->getStatus());
    }

    #[Test]
    public function getRetryCountReturnsConstructorValue(): void
    {
        $queue = new MailQueue(1, 'Subject', 'to@example.com', '', 'queued', 3, 0, 0);
        self::assertSame(3, $queue->getRetryCount());
    }

    #[Test]
    public function getScheduledAtReturnsConstructorValue(): void
    {
        $queue = new MailQueue(1, 'Subject', 'to@example.com', '', 'queued', 0, 1700000050, 0);
        self::assertSame(1700000050, $queue->getScheduledAt());
    }

    #[Test]
    public function getSentAtReturnsZeroWhenNotYetSent(): void
    {
        $queue = new MailQueue(1, 'Subject', 'to@example.com', '', 'queued', 0, 0, 0);
        self::assertSame(0, $queue->getSentAt());
    }

    #[Test]
    public function getSentAtReturnsTimestampAfterSend(): void
    {
        $queue = new MailQueue(1, 'Subject', 'to@example.com', '', 'sent', 0, 0, 1700000099);
        self::assertSame(1700000099, $queue->getSentAt());
    }

    // ── Instance isolation ───────────────────────────────────────────────────

    #[Test]
    public function twoInstancesAreIndependent(): void
    {
        $queueA = new MailQueue(1, 'First', 'a@example.com', 'Body A', 'queued', 0, 100, 0);
        $queueB = new MailQueue(2, 'Second', 'b@example.com', 'Body B', 'sent', 1, 200, 300);

        self::assertSame(1, $queueA->getUid());
        self::assertSame(2, $queueB->getUid());
        self::assertSame('queued', $queueA->getStatus());
        self::assertSame('sent', $queueB->getStatus());
        self::assertSame(0, $queueA->getRetryCount());
        self::assertSame(1, $queueB->getRetryCount());
    }
}
