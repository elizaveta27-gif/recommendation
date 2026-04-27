<?php

namespace App\Command\Consumers\View;

use App\Command\Consumers\BaseCommand;
use App\Command\Consumers\Config;
use App\Service\ViewService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:view-user',
    description: 'Consume view events and write trending products to Redis',
)]
class WriteUserViewCommand extends BaseCommand
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
        $this->viewService->writeUserView(
            (int)$data['product_id'],
            (int)$data['user_id'],
        );
    }

    protected function getQueueName(): string
    {
        return Config::USER_VIEW;
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
