<?php

namespace App\Command\Consumers;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class BaseCommand extends Command
{
    private AMQPStreamConnection $connection;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $hostRabbit,
        private readonly int $portRabbit,
        private readonly string $userRabbit,
        private readonly string $passwordRabbit,
    ) {

        $this->connection = new AMQPStreamConnection(
            $this->hostRabbit,
            $this->portRabbit,
            $this->userRabbit,
            $this->passwordRabbit,
        );


        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $channel = $this->connection->channel();

        $channel->queue_declare(
            $this->getQueueName(),
            passive: true
        );
        
        if ($this->readSequence()) 
        {
            $channel->basic_qos(null, 1, null);
        }

        $callback = function (AMQPMessage $msg) use ($io, $channel): void {
            try {
                $data = json_decode($msg->getBody(), true);
                $this->handle((array)$data);
                $msg->ack();
            } catch (\Throwable $e) {
                $config = $this->getConfigRetry(); 
                if ($config && $config['retry_count'] && $config['queue_name']) {
                   $this->sendRetry($config, $msg, $channel);
                } else {
                    $msg->nack(requeue: false);
                }

                $this->logger->error('Failed to create product', ['error' => $e->getMessage()]);
                $io->error($e->getMessage());
            }
        };

        $channel->basic_consume(
            $this->getQueueName(),
            '',
            false,
            false,
            false,
            false,
            $callback
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $this->connection->close();

        return Command::SUCCESS;
    }
    
    private function sendRetry(array $config, AMQPMessage $msg, AMQPChannel $channel): void
    {
        $headers = $msg->has('application_headers') ? $msg->get('application_headers')->getNativeData() : [];
        $retryCount = $headers['x-retry-count'] ?? 0;

        if ($retryCount < $config['retry_count']) {
            $delay = ($config['retry_delay'] ?? 5000) * ($retryCount + 1);

            $newMsg = new AMQPMessage($msg->getBody(), [
                'expiration' => (string) $delay,
                'application_headers' => [
                    'x-retry-count' => ['I', ++$retryCount],
                ],
            ]);
            $channel->basic_publish($newMsg, '', $config['queue_name']);
            $msg->ack();
        } else {
            $msg->nack(requeue: false);
        }
    }
    abstract protected function handle(array $data): void;
    abstract protected function getQueueName(): string;
    abstract protected function getConfigRetry(): array;
    abstract protected function readSequence(): bool;
}