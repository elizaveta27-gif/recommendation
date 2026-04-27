<?php

namespace App\Command\Consumers\Product;

use App\Command\Consumers\BaseCommand;
use App\Command\Consumers\Config;
use App\Service\ProductService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:update-product',
    description: 'Consume messages from RabbitMQ and update products',
)]
class UpdateProductCommand extends BaseCommand
{
    public function __construct(
        LoggerInterface $logger,
        string $hostRabbit,
        int $portRabbit,
        string $userRabbit,
        string $passwordRabbit,
        private readonly ProductService $productService,
    ) {
        parent::__construct($logger, $hostRabbit, $portRabbit, $userRabbit, $passwordRabbit);
    }

    protected function handle(array $data): void
    {
        $this->productService->update($data);
    }

    protected function getQueueName(): string
    {
        return Config::PRODUCT_UPDATED_QUEUE;
    }

    protected function getConfigRetry(): array
    {
        return [
            'queue_name'  => 'update_product.retry',
            'retry_count' => 3,
            'retry_delay' => 1000,
        ];
    }

    protected function readSequence(): bool
    {
        return true;
    }
}
