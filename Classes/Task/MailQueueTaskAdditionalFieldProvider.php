<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Task;

use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Scheduler\Task\Enumeration\Action;

/**
 * Additional field provider for the MailQueueTask scheduler task.
 */
class MailQueueTaskAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    public function getAdditionalFields(
        array &$taskInfo,
        $task,
        SchedulerModuleController $schedulerModule
    ): array {
        $currentSchedulerModuleAction = $schedulerModule->getCurrentAction();

        if ($currentSchedulerModuleAction->equals(Action::EDIT) && $task instanceof MailQueueTask) {
            $taskInfo['batchSize'] = $task->batchSize;
        }

        if (empty($taskInfo['batchSize'])) {
            $taskInfo['batchSize'] = 0;
        }

        $fieldId = 'task_batchSize';
        $fieldCode = '<input type="number" class="form-control" name="tx_scheduler[batchSize]" id="' . $fieldId . '" value="' . (int)$taskInfo['batchSize'] . '" min="0" />';

        return [
            $fieldId => [
                'code' => $fieldCode,
                'label' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang.xlf:task.mailqueue.batchSize',
                'cshKey' => '_MOD_system_txschedulerM1',
                'cshLabel' => $fieldId,
            ],
        ];
    }

    public function validateAdditionalFields(
        array &$submittedData,
        SchedulerModuleController $schedulerModule
    ): bool {
        $submittedData['batchSize'] = (int)($submittedData['batchSize'] ?? 0);
        return true;
    }

    public function saveAdditionalFields(array $submittedData, AbstractTask $task): void
    {
        if ($task instanceof MailQueueTask) {
            $task->batchSize = (int)$submittedData['batchSize'];
        }
    }
}
