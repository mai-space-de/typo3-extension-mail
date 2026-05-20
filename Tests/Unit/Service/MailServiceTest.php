<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Tests\Unit\Service;

use DateTimeImmutable;
use Maispace\MaiMail\Service\MailService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

final class MailServiceTest extends TestCase
{
    private ConnectionPool&MockObject $connectionPool;
    private ConfigurationManagerInterface&MockObject $configurationManager;
    private Mailer&MockObject $mailer;
    private MailService $subject;

    protected function setUp(): void
    {
        $this->connectionPool = $this->createMock(ConnectionPool::class);
        $this->configurationManager = $this->createMock(ConfigurationManagerInterface::class);
        $this->mailer = $this->createMock(Mailer::class);

        $this->subject = new MailService(
            $this->connectionPool,
            $this->configurationManager,
            $this->mailer,
        );
    }

    // ── queue() ──────────────────────────────────────────────────────────────

    #[Test]
    public function queueInsertsRowIntoQueueTable(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('insert')
            ->with(
                'tx_maimail_queue',
                self::callback(static function (array $data): bool {
                    return $data['recipient'] === 'user@example.com'
                        && $data['subject'] === 'Test Subject'
                        && $data['body'] === '<p>Hello</p>'
                        && $data['status'] === 'queued'
                        && $data['retry_count'] === 0
                        && $data['error_message'] === '';
                }),
            );

        $this->connectionPool->method('getConnectionForTable')->willReturn($connection);

        $this->subject->queue('user@example.com', 'Test Subject', '<p>Hello</p>');
    }

    #[Test]
    public function queueUsesProvidedScheduledAt(): void
    {
        $scheduledAt = new DateTimeImmutable('2025-12-31 12:00:00');

        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('insert')
            ->with(
                'tx_maimail_queue',
                self::callback(static function (array $data) use ($scheduledAt): bool {
                    return $data['scheduled_at'] === $scheduledAt->getTimestamp();
                }),
            );

        $this->connectionPool->method('getConnectionForTable')->willReturn($connection);

        $this->subject->queue('user@example.com', 'Subject', '<p>Body</p>', $scheduledAt);
    }

    #[Test]
    public function queueUsesFallbackScheduledAtWhenNotProvided(): void
    {
        $before = time();

        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('insert')
            ->with(
                'tx_maimail_queue',
                self::callback(static function (array $data) use ($before): bool {
                    return $data['scheduled_at'] >= $before
                        && $data['scheduled_at'] <= time() + 1;
                }),
            );

        $this->connectionPool->method('getConnectionForTable')->willReturn($connection);

        $this->subject->queue('user@example.com', 'Subject', '<p>Body</p>');
    }

    // ── dispatch() — success path ─────────────────────────────────────────────

    #[Test]
    public function dispatchSendsMailOnSuccessPath(): void
    {
        [$queueConnection, $logConnection] = $this->buildDispatchConnections();
        $this->mailer->expects(self::once())->method('send');
        $this->configurationManager->method('getConfiguration')->willReturn($this->validSenderConfig());

        $this->subject->dispatch($this->buildRow());
    }

    #[Test]
    public function dispatchMarksQueueEntryAsProcessingBeforeSend(): void
    {
        [$queueConnection, $logConnection] = $this->buildDispatchConnections();
        $this->mailer->method('send');
        $this->configurationManager->method('getConfiguration')->willReturn($this->validSenderConfig());

        $updateCalls = [];
        $queueConnection->method('update')
            ->willReturnCallback(static function (string $table, array $data) use (&$updateCalls): int {
                $updateCalls[] = $data['status'] ?? null;
                return 1;
            });

        $this->subject->dispatch($this->buildRow(uid: 5));

        self::assertContains('processing', $updateCalls);
    }

    #[Test]
    public function dispatchMarksQueueEntryAsSentAfterSuccessfulSend(): void
    {
        [$queueConnection, $logConnection] = $this->buildDispatchConnections();
        $this->mailer->method('send');
        $this->configurationManager->method('getConfiguration')->willReturn($this->validSenderConfig());

        $updateCalls = [];
        $queueConnection->method('update')
            ->willReturnCallback(static function (string $table, array $data) use (&$updateCalls): int {
                $updateCalls[] = $data['status'] ?? null;
                return 1;
            });

        $this->subject->dispatch($this->buildRow(uid: 5));

        self::assertContains('sent', $updateCalls);
    }

    #[Test]
    public function dispatchWritesLogEntryWithStatusSentOnSuccess(): void
    {
        [$queueConnection, $logConnection] = $this->buildDispatchConnections();
        $this->mailer->method('send');
        $this->configurationManager->method('getConfiguration')->willReturn($this->validSenderConfig());

        $logConnection->expects(self::once())
            ->method('insert')
            ->with(
                'tx_maimail_log',
                self::callback(static function (array $data): bool {
                    return $data['status'] === 'sent' && $data['error_message'] === '';
                }),
            );

        $this->subject->dispatch($this->buildRow());
    }

    #[Test]
    public function dispatchUsesSettingsEmailFromConfigurationManager(): void
    {
        [$queueConnection, $logConnection] = $this->buildDispatchConnections();
        $this->configurationManager->method('getConfiguration')
            ->willReturn($this->validSenderConfig());

        $this->mailer->expects(self::once())->method('send');

        $this->subject->dispatch($this->buildRow());
    }

    // ── dispatch() — failure path ─────────────────────────────────────────────

    #[Test]
    public function dispatchSetsStatusToQueuedOnFirstFailure(): void
    {
        [$queueConnection, $logConnection] = $this->buildDispatchConnections();
        $this->configurationManager->method('getConfiguration')->willReturn($this->validSenderConfig());
        $this->mailer->method('send')->willThrowException(new \RuntimeException('SMTP error'));

        $lastUpdateData = [];
        $queueConnection->method('update')
            ->willReturnCallback(static function (string $table, array $data) use (&$lastUpdateData): int {
                $lastUpdateData = $data;
                return 1;
            });

        $this->subject->dispatch($this->buildRow(retryCount: 0));

        // After first failure (retry_count was 0 → becomes 1, < 3) status stays 'queued'
        self::assertSame('queued', $lastUpdateData['status'] ?? null);
        self::assertSame(1, $lastUpdateData['retry_count'] ?? null);
    }

    #[Test]
    public function dispatchSetsStatusToFailedAfterThirdFailure(): void
    {
        [$queueConnection, $logConnection] = $this->buildDispatchConnections();
        $this->configurationManager->method('getConfiguration')->willReturn($this->validSenderConfig());
        $this->mailer->method('send')->willThrowException(new \RuntimeException('SMTP error'));

        $lastUpdateData = [];
        $queueConnection->method('update')
            ->willReturnCallback(static function (string $table, array $data) use (&$lastUpdateData): int {
                $lastUpdateData = $data;
                return 1;
            });

        // retry_count=2 means this is the 3rd attempt: 2+1=3 >= 3 → 'failed'
        $this->subject->dispatch($this->buildRow(retryCount: 2));

        self::assertSame('failed', $lastUpdateData['status'] ?? null);
        self::assertSame(3, $lastUpdateData['retry_count'] ?? null);
    }

    #[Test]
    public function dispatchWritesLogEntryWithStatusFailedOnError(): void
    {
        [$queueConnection, $logConnection] = $this->buildDispatchConnections();
        $this->configurationManager->method('getConfiguration')->willReturn($this->validSenderConfig());
        $this->mailer->method('send')->willThrowException(new \RuntimeException('Connection refused'));

        $logConnection->expects(self::once())
            ->method('insert')
            ->with(
                'tx_maimail_log',
                self::callback(static function (array $data): bool {
                    return $data['status'] === 'failed'
                        && str_contains($data['error_message'], 'Connection refused');
                }),
            );

        $this->subject->dispatch($this->buildRow());
    }

    #[Test]
    public function dispatchStoresErrorMessageInQueueEntryOnFailure(): void
    {
        [$queueConnection, $logConnection] = $this->buildDispatchConnections();
        $this->configurationManager->method('getConfiguration')->willReturn($this->validSenderConfig());
        $this->mailer->method('send')->willThrowException(new \RuntimeException('Timeout'));

        $lastUpdateData = [];
        $queueConnection->method('update')
            ->willReturnCallback(static function (string $table, array $data) use (&$lastUpdateData): int {
                $lastUpdateData = $data;
                return 1;
            });

        $this->subject->dispatch($this->buildRow(retryCount: 0));

        self::assertSame('Timeout', $lastUpdateData['error_message'] ?? null);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Build and wire queue + log Connection mocks; returns them as an array.
     *
     * @return array{0: Connection&MockObject, 1: Connection&MockObject}
     */
    private function buildDispatchConnections(): array
    {
        $queueConnection = $this->createMock(Connection::class);
        $logConnection = $this->createMock(Connection::class);

        $this->connectionPool->method('getConnectionForTable')
            ->willReturnCallback(
                static function (string $table) use ($queueConnection, $logConnection): Connection {
                    return match ($table) {
                        'tx_maimail_queue' => $queueConnection,
                        'tx_maimail_log' => $logConnection,
                        default => throw new \InvalidArgumentException('Unexpected table: ' . $table),
                    };
                },
            );

        return [$queueConnection, $logConnection];
    }

    /**
     * Build a minimal queue row array for dispatch().
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function buildRow(int $uid = 1, int $retryCount = 0, array $overrides = []): array
    {
        return array_merge([
            'uid' => $uid,
            'subject' => 'Test Mail',
            'recipient' => 'to@example.com',
            'body' => '<p>Content</p>',
            'retry_count' => $retryCount,
        ], $overrides);
    }

    /**
     * Return a settings array with a valid sender address so Address() does not throw.
     *
     * @return array<string, string>
     */
    private function validSenderConfig(): array
    {
        return [
            'defaultSenderEmail' => 'noreply@example.com',
            'defaultSenderName' => 'Test Sender',
        ];
    }
}
