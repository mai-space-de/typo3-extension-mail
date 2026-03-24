<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Controller;

use Maispace\MaiMail\Domain\Model\MailQueue;
use Maispace\MaiMail\Domain\Repository\MailQueueRepository;
use Maispace\MaiMail\Service\MailQueueService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Backend controller for managing the mail queue.
 */
class MailQueueController extends ActionController
{
    public function __construct(
        private readonly MailQueueRepository $mailQueueRepository,
        private readonly MailQueueService $mailQueueService,
        private readonly ModuleTemplateFactory $moduleTemplateFactory
    ) {
    }

    /**
     * List all queued mails.
     */
    public function indexAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $statusFilter = $this->request->hasArgument('status')
            ? (string)$this->request->getArgument('status')
            : '';

        if ($statusFilter !== '') {
            $mails = $this->mailQueueRepository->findByStatus($statusFilter);
        } else {
            $mails = $this->mailQueueRepository->findAll();
        }

        $moduleTemplate->assign('mails', $mails);
        $moduleTemplate->assign('statusFilter', $statusFilter);
        $moduleTemplate->assign('statuses', [
            MailQueue::STATUS_QUEUED,
            MailQueue::STATUS_SENDING,
            MailQueue::STATUS_SENT,
            MailQueue::STATUS_FAILED,
            MailQueue::STATUS_RETRY,
        ]);
        $moduleTemplate->assign('stats', $this->mailQueueService->getStats());

        return $moduleTemplate->renderResponse('MailQueue/Index');
    }

    /**
     * Show details of a single mail queue entry.
     */
    public function showAction(int $uid): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $mailQueue = $this->mailQueueRepository->findByUid($uid);
        if ($mailQueue === null) {
            $this->addFlashMessage(
                'Mail queue entry not found.',
                'Error',
                ContextualFeedbackSeverity::ERROR
            );
            return $this->redirect('index');
        }

        $moduleTemplate->assign('mail', $mailQueue);

        return $moduleTemplate->renderResponse('MailQueue/Show');
    }

    /**
     * Delete a mail queue entry.
     */
    public function deleteAction(int $uid): ResponseInterface
    {
        $removed = $this->mailQueueService->remove($uid);
        if ($removed) {
            $this->addFlashMessage('Mail queue entry deleted.', 'Success');
        } else {
            $this->addFlashMessage('Mail queue entry not found.', 'Error', ContextualFeedbackSeverity::ERROR);
        }
        return $this->redirect('index');
    }

    /**
     * Retry a failed mail queue entry.
     */
    public function retryAction(int $uid): ResponseInterface
    {
        $success = $this->mailQueueService->retry($uid);
        if ($success) {
            $this->addFlashMessage('Mail sent successfully.', 'Success');
        } else {
            $this->addFlashMessage('Failed to resend mail. Check error log.', 'Error', ContextualFeedbackSeverity::ERROR);
        }
        return $this->redirect('index');
    }

    /**
     * Immediately send a queued mail.
     */
    public function sendNowAction(int $uid): ResponseInterface
    {
        $mailQueue = $this->mailQueueRepository->findByUid($uid);
        if ($mailQueue === null) {
            $this->addFlashMessage('Mail queue entry not found.', 'Error', ContextualFeedbackSeverity::ERROR);
            return $this->redirect('index');
        }

        $success = $this->mailQueueService->sendNow($uid);
        if ($success) {
            $this->addFlashMessage('Mail sent successfully.', 'Success');
        } else {
            $this->addFlashMessage('Failed to send mail. Check error log.', 'Error', ContextualFeedbackSeverity::ERROR);
        }
        return $this->redirect('index');
    }
}
