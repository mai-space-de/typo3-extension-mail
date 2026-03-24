<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Service;

use Maispace\MaiMail\Domain\Model\MailQueue;
use Maispace\MaiMail\Domain\Repository\MailQueueRepository;
use Maispace\MaiMail\Event\MailQueuedEvent;
use Maispace\MaiMail\Event\MailSentEvent;
use Maispace\MaiMail\Event\MailFailedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * Service for managing the mail queue.
 */
class MailQueueService
{
    public function __construct(
        private readonly MailQueueRepository $mailQueueRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Add a new mail to the queue.
     *
     * @param string|array<string, string> $recipients Email address(es) as string or ['email' => 'name'] array
     * @param string[] $attachments File paths to attach
     */
    public function add(
        string $sender,
        string|array $recipients,
        string $subject,
        string $body,
        array $attachments = [],
        int $priority = 0,
        ?\DateTimeInterface $scheduledAt = null
    ): MailQueue {
        $mailQueue = new MailQueue();
        $mailQueue->setSender($sender);
        $mailQueue->setRecipients(
            is_array($recipients) ? json_encode($recipients, JSON_THROW_ON_ERROR) : json_encode([$recipients => ''], JSON_THROW_ON_ERROR)
        );
        $mailQueue->setSubject($subject);
        $mailQueue->setBody($body);
        $mailQueue->setAttachments(!empty($attachments) ? json_encode($attachments, JSON_THROW_ON_ERROR) : '');
        $mailQueue->setPriority($priority);
        $mailQueue->setScheduledAt($scheduledAt);
        $mailQueue->setStatus(MailQueue::STATUS_QUEUED);

        $this->mailQueueRepository->add($mailQueue);
        $this->persistenceManager->persistAll();

        $this->eventDispatcher->dispatch(new MailQueuedEvent($mailQueue));

        return $mailQueue;
    }

    /**
     * Remove a mail from the queue by UID.
     */
    public function remove(int $uid): bool
    {
        $mailQueue = $this->mailQueueRepository->findByUid($uid);
        if ($mailQueue === null) {
            return false;
        }
        $this->mailQueueRepository->remove($mailQueue);
        $this->persistenceManager->persistAll();
        return true;
    }

    /**
     * Remove all mails from the queue.
     */
    public function removeAll(): int
    {
        $all = $this->mailQueueRepository->findAll();
        $count = 0;
        foreach ($all as $mailQueue) {
            $this->mailQueueRepository->remove($mailQueue);
            $count++;
        }
        $this->persistenceManager->persistAll();
        return $count;
    }

    /**
     * Send all queued mails that are due.
     */
    public function sendAll(): int
    {
        $queued = $this->mailQueueRepository->findQueued();
        $count = 0;
        foreach ($queued as $mailQueue) {
            if ($this->sendSingle($mailQueue)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Send a batch of queued mails.
     */
    public function sendBatch(int $limit): int
    {
        $queued = $this->mailQueueRepository->findQueued();
        $count = 0;
        foreach ($queued as $mailQueue) {
            if ($count >= $limit) {
                break;
            }
            if ($this->sendSingle($mailQueue)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Retry a failed mail by UID.
     */
    public function retry(int $uid): bool
    {
        $mailQueue = $this->mailQueueRepository->findByUid($uid);
        if ($mailQueue === null) {
            return false;
        }

        $mailQueue->setStatus(MailQueue::STATUS_RETRY);
        $mailQueue->setErrorMessage('');
        $this->mailQueueRepository->update($mailQueue);
        $this->persistenceManager->persistAll();

        return $this->sendSingle($mailQueue);
    }

    /**
     * Get statistics about the mail queue.
     *
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        $countByStatus = $this->mailQueueRepository->countByStatus();

        return [
            'total' => array_sum($countByStatus),
            'queued' => $countByStatus[MailQueue::STATUS_QUEUED] ?? 0,
            'sending' => $countByStatus[MailQueue::STATUS_SENDING] ?? 0,
            'sent' => $countByStatus[MailQueue::STATUS_SENT] ?? 0,
            'failed' => $countByStatus[MailQueue::STATUS_FAILED] ?? 0,
            'retry' => $countByStatus[MailQueue::STATUS_RETRY] ?? 0,
        ];
    }

    /**
     * Send a single mail queue entry.
     */
    private function sendSingle(MailQueue $mailQueue): bool
    {
        $mailQueue->setStatus(MailQueue::STATUS_SENDING);
        $this->mailQueueRepository->update($mailQueue);
        $this->persistenceManager->persistAll();

        try {
            $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailMessage::class);

            $sender = $mailQueue->getSender();
            if (str_contains($sender, ':')) {
                [$senderName, $senderEmail] = explode(':', $sender, 2);
                $message->from(new \Symfony\Component\Mime\Address(trim($senderEmail), trim($senderName)));
            } else {
                $message->from($sender);
            }

            $recipients = $mailQueue->getRecipientsArray();
            foreach ($recipients as $email => $name) {
                $emailStr = is_string($email) ? $email : (string)$name;
                $nameStr = is_string($email) ? (string)$name : '';
                if ($emailStr === '' || !filter_var($emailStr, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }
                if ($nameStr !== '') {
                    $message->addTo(new \Symfony\Component\Mime\Address($emailStr, $nameStr));
                } else {
                    $message->addTo($emailStr);
                }
            }

            $message->subject($mailQueue->getSubject());

            $body = $mailQueue->getBody();
            if (str_contains($body, '<')) {
                $message->html($body);
                $message->text(strip_tags($body));
            } else {
                $message->text($body);
            }

            foreach ($mailQueue->getAttachmentsArray() as $attachment) {
                if (file_exists($attachment)) {
                    $message->attachFromPath($attachment);
                }
            }

            $message->send();

            $mailQueue->setStatus(MailQueue::STATUS_SENT);
            $mailQueue->setSentAt(new \DateTimeImmutable());
            $this->mailQueueRepository->update($mailQueue);
            $this->persistenceManager->persistAll();

            $this->eventDispatcher->dispatch(new MailSentEvent($mailQueue));

            return true;
        } catch (\Throwable $e) {
            $this->logger->error('Failed to send mail queue entry {uid}: {message}', [
                'uid' => $mailQueue->getUid(),
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            $mailQueue->setStatus(MailQueue::STATUS_FAILED);
            $mailQueue->setErrorMessage($e->getMessage());
            $mailQueue->setRetryCount($mailQueue->getRetryCount() + 1);
            $this->mailQueueRepository->update($mailQueue);
            $this->persistenceManager->persistAll();

            $this->eventDispatcher->dispatch(new MailFailedEvent($mailQueue, $e));

            return false;
        }
    }
}
