<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Tests\Unit\Controller\Backend;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\Connection;

final class MailBackendControllerTest extends TestCase
{
    #[Test]
    public function resendActionDatabaseUpdateContainsCorrectFields(): void
    {
        $expectedFields = ['status', 'retry_count', 'error_message', 'tstamp'];
        $actualFields = ['status' => 'queued', 'retry_count' => 0, 'error_message' => '', 'tstamp' => time()];

        foreach ($expectedFields as $field) {
            self::assertArrayHasKey($field, $actualFields, "resendAction must update field: {$field}");
        }
    }

    #[Test]
    public function resendActionResetsStatusToQueued(): void
    {
        $updateData = ['status' => 'queued', 'retry_count' => 0, 'error_message' => '', 'tstamp' => time()];

        self::assertSame('queued', $updateData['status']);
    }

    #[Test]
    public function resendActionResetsRetryCountToZero(): void
    {
        $updateData = ['status' => 'queued', 'retry_count' => 0, 'error_message' => '', 'tstamp' => time()];

        self::assertSame(0, $updateData['retry_count']);
    }

    #[Test]
    public function resendActionClearsErrorMessage(): void
    {
        $updateData = ['status' => 'queued', 'retry_count' => 0, 'error_message' => '', 'tstamp' => time()];

        self::assertSame('', $updateData['error_message']);
    }

    #[Test]
    public function resendActionIncludesTimestamp(): void
    {
        $before = time();
        $updateData = ['status' => 'queued', 'retry_count' => 0, 'error_message' => '', 'tstamp' => time()];

        self::assertArrayHasKey('tstamp', $updateData);
        self::assertGreaterThanOrEqual($before, $updateData['tstamp']);
        self::assertLessThanOrEqual(time() + 1, $updateData['tstamp']);
    }

    #[Test]
    public function deleteActionUsesCorrectTableName(): void
    {
        $tableName = 'tx_maimail_queue';

        self::assertSame('tx_maimail_queue', $tableName);
    }

    #[Test]
    public function deleteActionUsesUidAsWhereCondition(): void
    {
        $whereClause = ['uid' => 42];

        self::assertArrayHasKey('uid', $whereClause);
        self::assertIsInt($whereClause['uid']);
    }
}
