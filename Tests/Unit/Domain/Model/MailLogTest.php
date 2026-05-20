<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Tests\Unit\Domain\Model;

use Maispace\MaiMail\Domain\Model\MailLog;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MailLogTest extends TestCase
{
    // ── Constructor / getters ────────────────────────────────────────────────

    #[Test]
    public function getUidReturnsConstructorValue(): void
    {
        $log = new MailLog(42, 'Subject', 'to@example.com', 'sent', 1700000000, '');
        self::assertSame(42, $log->getUid());
    }

    #[Test]
    public function getSubjectReturnsConstructorValue(): void
    {
        $log = new MailLog(1, 'Hello World', 'to@example.com', 'sent', 1700000000, '');
        self::assertSame('Hello World', $log->getSubject());
    }

    #[Test]
    public function getRecipientReturnsConstructorValue(): void
    {
        $log = new MailLog(1, 'Subject', 'user@example.com', 'sent', 1700000000, '');
        self::assertSame('user@example.com', $log->getRecipient());
    }

    #[Test]
    public function getStatusReturnsConstructorValue(): void
    {
        $log = new MailLog(1, 'Subject', 'to@example.com', 'failed', 1700000000, '');
        self::assertSame('failed', $log->getStatus());
    }

    #[Test]
    public function getSentAtReturnsConstructorValue(): void
    {
        $log = new MailLog(1, 'Subject', 'to@example.com', 'sent', 1700000001, '');
        self::assertSame(1700000001, $log->getSentAt());
    }

    #[Test]
    public function getErrorMessageReturnsConstructorValue(): void
    {
        $log = new MailLog(1, 'Subject', 'to@example.com', 'failed', 1700000000, 'SMTP error 550');
        self::assertSame('SMTP error 550', $log->getErrorMessage());
    }

    #[Test]
    public function getErrorMessageReturnsEmptyStringWhenNone(): void
    {
        $log = new MailLog(1, 'Subject', 'to@example.com', 'sent', 1700000000, '');
        self::assertSame('', $log->getErrorMessage());
    }

    // ── Instance isolation ───────────────────────────────────────────────────

    #[Test]
    public function twoInstancesAreIndependent(): void
    {
        $logA = new MailLog(1, 'First', 'a@example.com', 'sent', 100, '');
        $logB = new MailLog(2, 'Second', 'b@example.com', 'failed', 200, 'Error');

        self::assertSame(1, $logA->getUid());
        self::assertSame(2, $logB->getUid());
        self::assertSame('First', $logA->getSubject());
        self::assertSame('Second', $logB->getSubject());
    }
}
