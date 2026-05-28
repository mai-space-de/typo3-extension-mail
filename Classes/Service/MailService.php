<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Service;

use DateTimeInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

final class MailService implements MailQueueInterface
{
    private const string TABLE_QUEUE = 'tx_maimail_queue';
    private const string TABLE_LOG = 'tx_maimail_log';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly ConfigurationManagerInterface $configurationManager,
        private readonly Mailer $mailer,
    ) {}

    /**
     * @param array<string, string> $headers
     */
    public function queue(string $recipient, string $subject, string $htmlBody, ?DateTimeInterface $scheduledAt = null, array $headers = []): void
    {
        $connection = $this->connectionPool->getConnectionForTable(self::TABLE_QUEUE);
        $timestamp = time();

        $connection->insert(self::TABLE_QUEUE, [
            'pid' => 0,
            'tstamp' => $timestamp,
            'crdate' => $timestamp,
            'subject' => $subject,
            'recipient' => $recipient,
            'body' => $htmlBody,
            'headers' => $headers !== [] ? json_encode($headers, JSON_THROW_ON_ERROR) : null,
            'status' => 'queued',
            'retry_count' => 0,
            'error_message' => '',
            'scheduled_at' => $scheduledAt?->getTimestamp() ?? $timestamp,
            'sent_at' => 0,
        ]);
    }

    /**
     * Read the configured max retry count from TypoScript settings.
     * Defaults to 3 if not set or non-numeric.
     */
    private function resolveMaxRetryCount(): int
    {
        try {
            $settings = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'MaiMail',
            );
            $value = $settings['maxRetryCount'] ?? 3;

            return is_numeric($value) ? (int) $value : 3;
        } catch (\Throwable) {
            return 3;
        }
    }

    public function dispatch(array $row): void
    {
        $queueConnection = $this->connectionPool->getConnectionForTable(self::TABLE_QUEUE);
        $logConnection = $this->connectionPool->getConnectionForTable(self::TABLE_LOG);
        $timestamp = time();
        $uid = (int) $row['uid'];

        $queueConnection->update(self::TABLE_QUEUE, ['status' => 'processing', 'tstamp' => $timestamp], ['uid' => $uid]);

        try {
            $settings = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'MaiMail',
            );
            $senderEmail = $settings['defaultSenderEmail']
                ?? $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']
                ?? '';
            $senderName = $settings['defaultSenderName']
                ?? $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']
                ?? '';

            $message = new MailMessage();
            $message->from(new Address($senderEmail, $senderName))
                ->to($row['recipient'])
                ->subject($row['subject'])
                ->html($row['body']);

            $headers = isset($row['headers']) && $row['headers'] !== ''
                ? json_decode($row['headers'], true, 512, JSON_THROW_ON_ERROR)
                : [];
            if (is_array($headers)) {
                foreach ($headers as $name => $value) {
                    $message->getHeaders()->addTextHeader($name, $value);
                }
            }

            $this->mailer->send($message);

            $queueConnection->update(
                self::TABLE_QUEUE,
                ['status' => 'sent', 'sent_at' => $timestamp, 'error_message' => '', 'tstamp' => $timestamp],
                ['uid' => $uid],
            );
            $logConnection->insert(self::TABLE_LOG, [
                'pid' => 0,
                'tstamp' => $timestamp,
                'crdate' => $timestamp,
                'subject' => $row['subject'],
                'recipient' => $row['recipient'],
                'status' => 'sent',
                'sent_at' => $timestamp,
                'error_message' => '',
            ]);
        } catch (\Throwable $throwable) {
            $retryCount = (int) $row['retry_count'] + 1;
            $maxRetryCount = $this->resolveMaxRetryCount();
            $status = $retryCount >= $maxRetryCount ? 'dead' : 'queued';

            $queueConnection->update(
                self::TABLE_QUEUE,
                ['status' => $status, 'retry_count' => $retryCount, 'error_message' => $throwable->getMessage(), 'tstamp' => $timestamp],
                ['uid' => $uid],
            );
            $logConnection->insert(self::TABLE_LOG, [
                'pid' => 0,
                'tstamp' => $timestamp,
                'crdate' => $timestamp,
                'subject' => $row['subject'],
                'recipient' => $row['recipient'],
                'status' => 'failed',
                'sent_at' => $timestamp,
                'error_message' => $throwable->getMessage(),
            ]);
        }
    }
}
