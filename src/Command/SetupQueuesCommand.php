<?php

namespace App\Command;

use App\Command\Consumers\Config;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:setup-queues',
    description: 'Declare all RabbitMQ queues and exchanges',
)]
class SetupQueuesCommand extends Command
{
    public function __construct(
        private readonly string $rabbitHost = 'rabbit-mq',
        private readonly int $rabbitPort = 5672,
        private readonly string $rabbitUser = 'user',
        private readonly string $rabbitPass = 'password',
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $connection = new AMQPStreamConnection(
            $this->rabbitHost,
            $this->rabbitPort,
            $this->rabbitUser,
            $this->rabbitPass,
        );

        $channel = $connection->channel();

        foreach (Config::EXCHANGERS as $name => $arguments) {
            $channel->exchange_declare(
                $name,
                $arguments['type'] ?? 'direct',
                $arguments['passive'] ?? false,
                $arguments['durable'] ?? true,
                $arguments['auto_delete'] ?? false,
            );
            $io->text("Exchange declared: $name");
        }
        
        
        foreach (Config::QUEUES as $name => $arguments) {
            $channel->queue_declare(
                $name,
                passive: $arguments['passive'] ?? false,
                durable: $arguments['durable'] ?? true,
                exclusive: $arguments['exclusive'] ?? false,
                auto_delete: $arguments['auto_delete'] ?? false,
                nowait: $arguments['nowait'] ?? false,
                arguments: $arguments['arguments'] ?? [],
            );
            
            if (!empty($arguments['exchange'])) {
                $channel->queue_bind($name, $arguments['exchange']);
            }
            
            $io->text("Queue declared: $name");
        }

        $channel->close();
        $connection->close();

        $io->success('All queues declared successfully.');

        return Command::SUCCESS;
    }
}
