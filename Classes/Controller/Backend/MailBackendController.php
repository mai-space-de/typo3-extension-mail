<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Controller\Backend;

use Maispace\MaiBase\Controller\Backend\AbstractBackendController;
use Maispace\MaiMail\Domain\Repository\MailLogRepository;
use Maispace\MaiMail\Domain\Repository\MailQueueRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\IconFactory;

#[AsController]
class MailBackendController extends AbstractBackendController
{
    private const string TABLE_QUEUE = 'tx_maimail_queue';

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        IconFactory $iconFactory,
        private readonly MailQueueRepository $mailQueueRepository,
        private readonly MailLogRepository $mailLogRepository,
        private readonly ConnectionPool $connectionPool,
    ) {
        parent::__construct($moduleTemplateFactory, $iconFactory);
    }

    public function indexAction(): ResponseInterface
    {
        $moduleTemplate = $this->createModuleTemplate();
        $this->addShortcutButton($moduleTemplate, 'mai_mail', 'Mail Queue');
        $this->assignMultiple($moduleTemplate, [
            'queuedMails' => $this->mailQueueRepository->findAll(),
            'deadLetters' => $this->mailQueueRepository->findByStatus('dead'),
            'loggedMails' => $this->mailLogRepository->findRecent(50),
        ]);
        return $this->renderModuleResponse($moduleTemplate, 'Index');
    }

    public function resendAction(): ResponseInterface
    {
        $uid = (int) $this->request->getArgument('uid');
        $this->connectionPool->getConnectionForTable(self::TABLE_QUEUE)->update(
            self::TABLE_QUEUE,
            ['status' => 'queued', 'retry_count' => 0, 'error_message' => '', 'tstamp' => time()],
            ['uid' => $uid],
        );
        $this->flashSuccess('Mail re-queued successfully.');
        return $this->redirect('index');
    }

    public function deleteAction(): ResponseInterface
    {
        $uid = (int) $this->request->getArgument('uid');
        $this->connectionPool->getConnectionForTable(self::TABLE_QUEUE)->delete(
            self::TABLE_QUEUE,
            ['uid' => $uid],
        );
        $this->flashSuccess('Mail deleted from queue.');
        return $this->redirect('index');
    }
}
