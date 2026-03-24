<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Tests\Unit\Service;

use Maispace\MaiMail\Domain\Model\MailQueue;
use Maispace\MaiMail\Domain\Repository\MailQueueRepository;
use Maispace\MaiMail\Event\MailFailedEvent;
use Maispace\MaiMail\Event\MailQueuedEvent;
use Maispace\MaiMail\Service\MailQueueService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\NullLogger;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

#[CoversClass(MailQueueService::class)]
final class MailQueueServiceTest extends TestCase
{
    private MailQueueRepository&MockObject $repository;
    private PersistenceManagerInterface&MockObject $persistenceManager;
    private EventDispatcherInterface&MockObject $eventDispatcher;
    private MailQueueService $subject;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MailQueueRepository::class);
        $this->persistenceManager = $this->createMock(PersistenceManagerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->subject = new MailQueueService(
            $this->repository,
            $this->persistenceManager,
            $this->eventDispatcher,
            new NullLogger()
        );
    }

    #[Test]
    public function addCreatesMailQueueEntryAndDispatchesEvent(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('add')
            ->with(self::isInstanceOf(MailQueue::class));

        $this->persistenceManager
            ->expects(self::atLeastOnce())
            ->method('persistAll');

        $this->eventDispatcher
            ->expects(self::atLeastOnce())
            ->method('dispatch')
            ->with(self::isInstanceOf(MailQueuedEvent::class));

        $result = $this->subject->add(
            'sender@example.com',
            ['recipient@example.com' => 'Recipient'],
            'Test Subject',
            '<p>Test Body</p>'
        );

        self::assertInstanceOf(MailQueue::class, $result);
        self::assertSame('sender@example.com', $result->getSender());
        self::assertSame('Test Subject', $result->getSubject());
        self::assertSame(MailQueue::STATUS_QUEUED, $result->getStatus());
    }

    #[Test]
    public function addWithStringRecipientEncodesCorrectly(): void
    {
        $this->repository->method('add');
        $this->persistenceManager->method('persistAll');
        $this->eventDispatcher->method('dispatch');

        $result = $this->subject->add(
            'sender@example.com',
            'recipient@example.com',
            'Test',
            'Body'
        );

        $recipients = $result->getRecipientsArray();
        self::assertArrayHasKey('recipient@example.com', $recipients);
    }

    #[Test]
    public function addWithScheduledAtSetsScheduledAt(): void
    {
        $this->repository->method('add');
        $this->persistenceManager->method('persistAll');
        $this->eventDispatcher->method('dispatch');

        $scheduledAt = new \DateTimeImmutable('+1 day');
        $result = $this->subject->add(
            'sender@example.com',
            ['recipient@example.com' => ''],
            'Test',
            'Body',
            [],
            0,
            $scheduledAt
        );

        self::assertSame($scheduledAt, $result->getScheduledAt());
    }

    #[Test]
    public function removeReturnsFalseWhenUidNotFound(): void
    {
        $this->repository
            ->method('findByUid')
            ->with(999)
            ->willReturn(null);

        self::assertFalse($this->subject->remove(999));
    }

    #[Test]
    public function removeReturnsTrueAndDeletesWhenFound(): void
    {
        $mailQueue = new MailQueue();

        $this->repository
            ->method('findByUid')
            ->with(1)
            ->willReturn($mailQueue);

        $this->repository
            ->expects(self::once())
            ->method('remove')
            ->with($mailQueue);

        $this->persistenceManager
            ->expects(self::once())
            ->method('persistAll');

        self::assertTrue($this->subject->remove(1));
    }

    #[Test]
    public function getStatsReturnsCorrectStructure(): void
    {
        $this->repository
            ->method('countByStatus')
            ->willReturn([
                'queued' => 5,
                'sent' => 10,
                'failed' => 2,
                'retry' => 1,
                'sending' => 0,
            ]);

        $stats = $this->subject->getStats();

        self::assertArrayHasKey('total', $stats);
        self::assertArrayHasKey('queued', $stats);
        self::assertArrayHasKey('sent', $stats);
        self::assertArrayHasKey('failed', $stats);
        self::assertArrayHasKey('retry', $stats);
        self::assertArrayHasKey('sending', $stats);
        self::assertSame(18, $stats['total']);
        self::assertSame(5, $stats['queued']);
        self::assertSame(10, $stats['sent']);
        self::assertSame(2, $stats['failed']);
    }

    #[Test]
    public function retryReturnsFalseWhenUidNotFound(): void
    {
        $this->repository
            ->method('findByUid')
            ->with(999)
            ->willReturn(null);

        self::assertFalse($this->subject->retry(999));
    }

    #[Test]
    public function removeAllRemovesAllEntriesAndReturnsCount(): void
    {
        $mail1 = new MailQueue();
        $mail2 = new MailQueue();

        $queryResult = new \ArrayObject([$mail1, $mail2]);

        $this->repository
            ->method('findAll')
            ->willReturn($queryResult);

        $this->repository
            ->expects(self::exactly(2))
            ->method('remove');

        $this->persistenceManager
            ->expects(self::once())
            ->method('persistAll');

        $count = $this->subject->removeAll();
        self::assertSame(2, $count);
    }
}
