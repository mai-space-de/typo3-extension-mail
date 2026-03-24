<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Controller;

use Maispace\MaiMail\Service\MailQueueService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Backend controller for composing and sending emails.
 */
class ComposeController extends ActionController
{
    public function __construct(
        private readonly MailQueueService $mailQueueService,
        private readonly ModuleTemplateFactory $moduleTemplateFactory
    ) {}

    /**
     * Show compose form.
     */
    public function indexAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->assign('formData', [
            'sender' => $GLOBALS['BE_USER']->user['email'] ?? '',
            'recipients' => '',
            'subject' => '',
            'body' => '',
            'scheduledAt' => '',
            'priority' => 0,
            'sendMode' => 'now',
        ]);
        return $moduleTemplate->renderResponse('Compose/Index');
    }

    /**
     * Send or queue the composed email.
     */
    public function sendAction(): ResponseInterface
    {
        $formData = $this->request->getArgument('formData');
        if (!is_array($formData)) {
            $formData = [];
        }

        $sender = trim((string)($formData['sender'] ?? ''));
        $recipientsRaw = trim((string)($formData['recipients'] ?? ''));
        $subject = trim((string)($formData['subject'] ?? ''));
        $body = (string)($formData['body'] ?? '');
        $priority = (int)($formData['priority'] ?? 0);
        $sendMode = (string)($formData['sendMode'] ?? 'now');
        $scheduledAtRaw = trim((string)($formData['scheduledAt'] ?? ''));

        if ($sender === '' || $recipientsRaw === '' || $subject === '') {
            $this->addFlashMessage(
                'Sender, recipients and subject are required.',
                'Validation Error',
                ContextualFeedbackSeverity::ERROR
            );
            return $this->redirect('index');
        }

        // Parse recipients (comma separated list of email or "Name <email>")
        $recipients = $this->parseRecipients($recipientsRaw);

        $scheduledAt = null;
        if ($sendMode === 'scheduled' && $scheduledAtRaw !== '') {
            try {
                $scheduledAt = new \DateTimeImmutable($scheduledAtRaw);
            } catch (\Exception $e) {
                $this->addFlashMessage(
                    'Invalid scheduled date/time.',
                    'Validation Error',
                    ContextualFeedbackSeverity::ERROR
                );
                return $this->redirect('index');
            }
        }

        $mailQueue = $this->mailQueueService->add(
            $sender,
            $recipients,
            $subject,
            $body,
            [],
            $priority,
            $scheduledAt
        );

        if ($sendMode === 'now') {
            $sent = $this->mailQueueService->retry($mailQueue->getUid());
            if ($sent) {
                $this->addFlashMessage('Email sent successfully.', 'Success');
            } else {
                $this->addFlashMessage('Email queued but sending failed. Check mail queue.', 'Warning', ContextualFeedbackSeverity::WARNING);
            }
        } else {
            $this->addFlashMessage('Email added to queue.', 'Success');
        }

        return $this->redirect('index');
    }

    /**
     * Parse a comma-separated recipients string into an array.
     *
     * @return array<string, string>
     */
    private function parseRecipients(string $raw): array
    {
        $result = [];
        foreach (explode(',', $raw) as $entry) {
            $entry = trim($entry);
            if (preg_match('/^(.+?)\s*<([^>]+)>$/', $entry, $matches)) {
                $result[trim($matches[2])] = trim($matches[1]);
            } elseif (filter_var($entry, FILTER_VALIDATE_EMAIL)) {
                $result[$entry] = '';
            }
        }
        return $result;
    }
}
