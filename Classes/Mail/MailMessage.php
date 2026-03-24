<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Mail;

use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\MailMessage as CoreMailMessage;

/**
 * Extended MailMessage with additional helpers for the mai_mail extension.
 */
class MailMessage extends CoreMailMessage
{
    /**
     * Queue this message via the MailQueueService instead of sending immediately.
     *
     * @param int $priority Higher values = higher priority (default: 0)
     * @param \DateTimeInterface|null $scheduledAt Schedule for future sending
     * @param string[] $attachmentPaths Absolute paths to files to attach
     */
    public function queue(
        int $priority = 0,
        ?\DateTimeInterface $scheduledAt = null,
        array $attachmentPaths = []
    ): bool {
        /** @var \Maispace\MaiMail\Service\MailQueueService $service */
        $service = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \Maispace\MaiMail\Service\MailQueueService::class
        );

        $fromAddresses = $this->getFrom();
        $firstFrom = reset($fromAddresses);
        $senderString = $firstFrom instanceof Address
            ? $firstFrom->getName() . ':' . $firstFrom->getAddress()
            : (string)$firstFrom;

        $recipients = [];
        foreach ($this->getTo() as $address) {
            if ($address instanceof Address) {
                $recipients[$address->getAddress()] = $address->getName();
            } else {
                $recipients[(string)$address] = '';
            }
        }

        $body = $this->getHtmlBody() ?? $this->getTextBody() ?? '';

        $mailQueue = $service->add(
            $senderString,
            $recipients,
            (string)$this->getSubject(),
            (string)$body,
            $attachmentPaths,
            $priority,
            $scheduledAt
        );

        return $mailQueue->getUid() > 0;
    }

    /**
     * Add multiple recipients from an array of addresses.
     *
     * @param array<string, string> $recipients Associative array of email => name
     */
    public function addRecipients(array $recipients): static
    {
        foreach ($recipients as $email => $name) {
            $this->addTo(new Address($email, $name));
        }
        return $this;
    }

    /**
     * Set the HTML body and automatically generate a plain text alternative.
     */
    public function htmlWithTextFallback(string $html): static
    {
        $this->html($html);
        $this->text(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $html)));
        return $this;
    }
}
