<?php

namespace App\Command\Consumers\Product;

use App\Command\Consumers\BaseCommand;
use App\Command\Consumers\Config;
use App\Service\ProductService;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:create-product',
    description: 'Consume messages from RabbitMQ and create products',
)]
class CreateProductCommand extends BaseCommand
{
    private AMQPStreamConnection $connection;
    
    public function __construct(
        LoggerInterface $logger, 
        string $hostRabbit,
        int $portRabbit,
        string $userRabbit,
        string $passwordRabbit,
        private ProductService $productService,
    )
    {
        parent::__construct($logger, $hostRabbit, $portRabbit, $userRabbit, $passwordRabbit);
    }

    protected function handle(array $data): void
    {
        $this->productService->add($data);
    }

    protected function getConfigRetry(): array
    {
        return [
            'queue_name'  => 'create_product.retry',
            'retry_count' => 3,
            'retry_delay' => 1000,
        ];
    }

    protected function getQueueName(): string
    {
        return Config::PRODUCT_CREATED_QUEUE;
    }

    protected function readSequence(): bool
    {
        return true;
    }
}
