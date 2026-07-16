<?php

namespace App\Command;

use App\Repository\BusinessRepository;
use App\Service\BusinessStatsService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:business-stats',
    description: 'Displays a live statistical overview of all businesses in the terminal.',
)]
class GenerateReportCommand extends Command
{
    public function __construct(
        private readonly BusinessRepository   $businessRepository,
        private readonly BusinessStatsService $statsService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $businesses = $this->businessRepository->findAll();

        if (empty($businesses)) {
            $io->warning('No businesses found in the database.');
            return Command::SUCCESS;
        }

        $tableData = [];

        foreach ($businesses as $business) {
            $stats = $this->statsService->calculateForBusiness($business);

            $tableData[] = [
                $business->getName(),
                $stats['packages_sold'],
                '$' . number_format($stats['total_revenue'], 2)
            ];
        }

        $io->title('Business Statistics Overview');
        $io->table(['Business Name', 'Packages Sold', 'Total Revenue'], $tableData);
        $io->success('Statistics generated successfully.');

        return Command::SUCCESS;
    }
}
