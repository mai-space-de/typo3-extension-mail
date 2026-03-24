<?php

declare(strict_types=1);

namespace Maispace\MaiMail\EventListener;

use Maispace\MaiMail\Event\MailFailedEvent;
use Maispace\MaiMail\Event\MailQueuedEvent;
use Maispace\MaiMail\Event\MailSentEvent;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;

/**
 * Listens to mail queue events and logs them.
 */
#[AsEventListener(identifier: 'mai-mail/mail-queued')]
#[AsEventListener(identifier: 'mai-mail/mail-sent')]
#[AsEventListener(identifier: 'mai-mail/mail-failed')]
final class MailQueueEventListener
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function __invoke(MailQueuedEvent|MailSentEvent|MailFailedEvent $event): void
    {
        match (true) {
            $event instanceof MailQueuedEvent => $this->onMailQueued($event),
            $event instanceof MailSentEvent => $this->onMailSent($event),
            $event instanceof MailFailedEvent => $this->onMailFailed($event),
        };
    }

    private function onMailQueued(MailQueuedEvent $event): void
    {
        $mail = $event->getMailQueue();
        $this->logger->info('Mail queued: {subject} (UID: {uid})', [
            'subject' => $mail->getSubject(),
            'uid' => $mail->getUid(),
            'sender' => $mail->getSender(),
        ]);
    }

    private function onMailSent(MailSentEvent $event): void
    {
        $mail = $event->getMailQueue();
        $this->logger->info('Mail sent: {subject} (UID: {uid})', [
            'subject' => $mail->getSubject(),
            'uid' => $mail->getUid(),
            'sender' => $mail->getSender(),
        ]);
    }

    private function onMailFailed(MailFailedEvent $event): void
    {
        $mail = $event->getMailQueue();
        $this->logger->error('Mail failed: {subject} (UID: {uid}) - {error}', [
            'subject' => $mail->getSubject(),
            'uid' => $mail->getUid(),
            'error' => $event->getException()->getMessage(),
            'exception' => $event->getException(),
        ]);
    }
}
