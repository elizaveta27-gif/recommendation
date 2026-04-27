<?php

namespace App\Command\Scripts;

use App\Service\TrendsService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:calculation-trends',
    description: 'Consume messages from RabbitMQ and update products',
)]
class CalculationTrends extends Command
{
    
    public function __construct(
        private readonly TrendsService   $recommendationService,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        try {
            $this->recommendationService->generateTrends();
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to generate trends', ['error' => $exception->getMessage()]);
        }

        return Command::SUCCESS;
    }
}