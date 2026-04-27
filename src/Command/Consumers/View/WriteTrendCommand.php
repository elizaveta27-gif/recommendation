<?php

namespace App\Command\Consumers\View;

use App\Command\Consumers\BaseCommand;
use App\Command\Consumers\Config;
use App\Service\ViewService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:view-write-trend',
    description: 'Consume view events and write trending products to Redis',
)]
class WriteTrendCommand extends BaseCommand
{
    public function __construct(
        LoggerInterface $logger,
        string $hostRabbit,
        int $portRabbit,
        string $userRabbit,
        string $passwordRabbit,
        private readonly ViewService $viewService,
    ) {
        parent::__construct($logger, $hostRabbit, $portRabbit, $userRabbit, $passwordRabbit);
    }

    protected function handle(array $data): void
    {
        $this->viewService->writeTrend(
            (int)$data['product_id'],
        );
    }

    protected function getQueueName(): string
    {
        return Config::TRENDS_VIEW;
    }

    protected function getConfigRetry(): array
    {
        return [];
    }

    protected function readSequence(): bool
    {
        return false;
    }
}
