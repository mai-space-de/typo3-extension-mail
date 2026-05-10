<?php

declare(strict_types=1);

namespace Maispace\MaiMail\Command;

use Maispace\MaiMail\Service\MailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

#[AsCommand(
    name: 'mail:process-queue',
    description: 'Process the mail queue and dispatch pending emails',
)]
final class ProcessMailQueueCommand extends Command
{
    private const string TABLE_QUEUE = 'tx_maimail_queue';

    public function __construct(
        private readonly MailService $mailService,
        private readonly ConnectionPool $connectionPool,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_QUEUE);
        $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());

        $rows = $queryBuilder
            ->select('*')
            ->from(self::TABLE_QUEUE)
            ->where(
                $queryBuilder->expr()->eq('status', $queryBuilder->createNamedParameter('queued')),
                $queryBuilder->expr()->lte('scheduled_at', $queryBuilder->createNamedParameter(time(), \PDO::PARAM_INT))
            )
            ->orderBy('scheduled_at', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        if ($rows === []) {
            $io->success('No queued mails found.');
            return Command::SUCCESS;
        }

        $processed = 0;
        $failed = 0;

        foreach ($rows as $row) {
            $statusBefore = (string)$row['status'];
            $this->mailService->dispatch($row);

            $updated = $this->connectionPool->getQueryBuilderForTable(self::TABLE_QUEUE)
                ->select('status')
                ->from(self::TABLE_QUEUE)
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter((int)$row['uid'], \PDO::PARAM_INT))
                )
                ->executeQuery()
                ->fetchOne();

            $updated === 'sent' ? $processed++ : $failed++;
        }

        $io->success(sprintf('Processed %d mail(s), %d failure(s).', $processed, $failed));

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
