<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Backend controller for IMAP/POP3 inbox viewing.
 */
class InboxController extends ActionController
{
    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory
    ) {}

    /**
     * List inbox messages.
     */
    public function indexAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $config = $this->getInboxConfig();
        $messages = [];
        $error = null;

        if (!empty($config['host'])) {
            try {
                $messages = $this->fetchMessages($config);
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        $moduleTemplate->assign('messages', $messages);
        $moduleTemplate->assign('error', $error);
        $moduleTemplate->assign('configured', !empty($config['host']));

        return $moduleTemplate->renderResponse('Inbox/Index');
    }

    /**
     * Read a specific message.
     */
    public function readAction(int $messageNumber): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $config = $this->getInboxConfig();
        $message = null;
        $error = null;

        if (!empty($config['host'])) {
            try {
                $message = $this->fetchMessage($config, $messageNumber);
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        $moduleTemplate->assign('message', $message);
        $moduleTemplate->assign('messageNumber', $messageNumber);
        $moduleTemplate->assign('error', $error);

        return $moduleTemplate->renderResponse('Inbox/Index');
    }

    /**
     * Delete a message from inbox.
     */
    public function deleteAction(int $messageNumber): ResponseInterface
    {
        $config = $this->getInboxConfig();

        if (!empty($config['host'])) {
            try {
                $this->deleteMessage($config, $messageNumber);
                $this->addFlashMessage('Message deleted successfully.', 'Success');
            } catch (\Throwable $e) {
                $this->addFlashMessage(
                    'Failed to delete message: ' . $e->getMessage(),
                    'Error',
                    ContextualFeedbackSeverity::ERROR
                );
            }
        }

        return $this->redirect('index');
    }

    /**
     * Get inbox configuration from TYPO3 extension configuration.
     *
     * @return array<string, mixed>
     */
    private function getInboxConfig(): array
    {
        $extConf = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['mai_mail'] ?? [];
        return [
            'host' => (string)($extConf['inboxHost'] ?? ''),
            'port' => (int)($extConf['inboxPort'] ?? 993),
            'protocol' => (string)($extConf['inboxProtocol'] ?? 'imap'),
            'username' => (string)($extConf['inboxUsername'] ?? ''),
            'password' => (string)($extConf['inboxPassword'] ?? ''),
            'encryption' => (string)($extConf['inboxEncryption'] ?? 'ssl'),
        ];
    }

    /**
     * Fetch messages from an IMAP/POP3 mailbox using PHP's imap extension.
     *
     * @param array<string, mixed> $config
     * @return array<int, array<string, mixed>>
     */
    private function fetchMessages(array $config): array
    {
        if (!extension_loaded('imap')) {
            throw new \RuntimeException('PHP imap extension is not loaded. Please enable it to use the inbox feature.');
        }

        $mailbox = $this->buildMailboxString($config);
        $connection = $this->openImapConnection($mailbox, $config);

        try {
            $count = imap_num_msg($connection);
            $messages = [];
            $limit = min($count, 50);

            for ($i = $count; $i > $count - $limit; $i--) {
                $header = imap_headerinfo($connection, $i);
                if ($header === false) {
                    continue;
                }
                $messages[] = [
                    'number' => $i,
                    'subject' => isset($header->subject) ? imap_utf8($header->subject) : '(no subject)',
                    'from' => isset($header->from[0]) ? $this->formatAddress($header->from[0]) : '',
                    'date' => isset($header->date) ? $header->date : '',
                    'seen' => isset($header->Seen) && $header->Seen === 'S',
                ];
            }

            return $messages;
        } finally {
            imap_close($connection);
        }
    }

    /**
     * Fetch a single message.
     *
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function fetchMessage(array $config, int $messageNumber): array
    {
        if (!extension_loaded('imap')) {
            throw new \RuntimeException('PHP imap extension is not loaded.');
        }

        $mailbox = $this->buildMailboxString($config);
        $connection = $this->openImapConnection($mailbox, $config);

        try {
            $header = imap_headerinfo($connection, $messageNumber);
            if ($header === false) {
                throw new \RuntimeException('Message not found.');
            }

            $body = imap_fetchbody($connection, $messageNumber, '1');

            return [
                'number' => $messageNumber,
                'subject' => isset($header->subject) ? imap_utf8($header->subject) : '(no subject)',
                'from' => isset($header->from[0]) ? $this->formatAddress($header->from[0]) : '',
                'to' => isset($header->to[0]) ? $this->formatAddress($header->to[0]) : '',
                'date' => isset($header->date) ? $header->date : '',
                'body' => $body,
            ];
        } finally {
            imap_close($connection);
        }
    }

    /**
     * Delete a message from the mailbox.
     *
     * @param array<string, mixed> $config
     */
    private function deleteMessage(array $config, int $messageNumber): void
    {
        if (!extension_loaded('imap')) {
            throw new \RuntimeException('PHP imap extension is not loaded.');
        }

        $mailbox = $this->buildMailboxString($config);
        $connection = $this->openImapConnection($mailbox, $config);

        try {
            imap_delete($connection, (string)$messageNumber);
            imap_expunge($connection);
        } finally {
            imap_close($connection);
        }
    }

    /**
     * Open an IMAP connection with sanitized credentials.
     *
     * @param array<string, mixed> $config
     * @return \IMAP\Connection
     */
    private function openImapConnection(string $mailbox, array $config): mixed
    {
        // Strip non-printable ASCII characters from username to prevent connection string injection
        $username = (string)preg_replace('/[^\x20-\x7E]/', '', (string)$config['username']);
        $password = (string)$config['password'];

        $connection = @imap_open($mailbox, $username, $password);
        if ($connection === false) {
            throw new \RuntimeException('Could not connect to mail server: ' . imap_last_error());
        }
        return $connection;
    }

    /**
     * Build the mailbox connection string.
     *
     * @param array<string, mixed> $config
     */
    private function buildMailboxString(array $config): string
    {
        $encryption = $config['encryption'] === 'ssl' ? '/ssl' : '';
        $protocol = strtolower((string)$config['protocol']);
        return '{' . $config['host'] . ':' . $config['port'] . '/' . $protocol . $encryption . '}INBOX';
    }

    /**
     * Format an address object into a readable string.
     *
     * @param object $address
     */
    private function formatAddress(object $address): string
    {
        $name = isset($address->personal) ? imap_utf8($address->personal) : '';
        $email = ($address->mailbox ?? '') . '@' . ($address->host ?? '');
        return $name !== '' ? "{$name} <{$email}>" : $email;
    }
}
