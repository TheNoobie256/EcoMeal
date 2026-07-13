<?php

namespace App\Command;

use App\Repository\OrderRepository;
use App\Repository\PackageRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:generate-report',
    description: 'Generates a read-only daily sales report text file.',
)]
class GenerateReportCommand extends Command
{
    public function __construct(
        private PackageRepository $packageRepository,
        private OrderRepository $orderRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filesystem = new Filesystem();

        $totalPackages = count($this->packageRepository->findAll());
        $orders = $this->orderRepository->findAll();
        $totalOrders = count($orders);

        $totalRevenue = 0;
        foreach ($orders as $order) {
            $totalRevenue += $order->getPackage()->getPrice();
        }

        $date = (new \DateTime())->format('Y-m-d H:i:s');
        $reportContent = "====================================\n";
        $reportContent .= "DAILY SYSTEM REPORT: {$date}\n";
        $reportContent .= "====================================\n";
        $reportContent .= "Total Packages on Platform: {$totalPackages}\n";
        $reportContent .= "Total Packages Sold: {$totalOrders}\n";
        $reportContent .= "Total Generated Revenue: $" . number_format($totalRevenue, 2) . "\n\n";

        $reportPath = 'var/reports/daily_summary.txt';
        $filesystem->appendToFile($reportPath, $reportContent);

        $io->success('Report generated successfully at ' . $reportPath);

        return Command::SUCCESS;
    }
}
