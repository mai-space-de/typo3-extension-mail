<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Controller;

use Maispace\MaiMail\Service\MailQueueService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Backend controller for mail queue statistics.
 */
class StatsController extends ActionController
{
    public function __construct(
        private readonly MailQueueService $mailQueueService,
        private readonly ModuleTemplateFactory $moduleTemplateFactory
    ) {
    }

    /**
     * Show statistics dashboard.
     */
    public function indexAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $stats = $this->mailQueueService->getStats();

        $moduleTemplate->assign('stats', $stats);
        $moduleTemplate->assign('successRate', $this->calculateSuccessRate($stats));

        return $moduleTemplate->renderResponse('Stats/Index');
    }

    /**
     * Calculate the success rate as a percentage.
     *
     * @param array<string, mixed> $stats
     */
    private function calculateSuccessRate(array $stats): float
    {
        $total = (int)($stats['sent'] ?? 0) + (int)($stats['failed'] ?? 0);
        if ($total === 0) {
            return 0.0;
        }
        return round((int)($stats['sent'] ?? 0) / $total * 100, 2);
    }
}
