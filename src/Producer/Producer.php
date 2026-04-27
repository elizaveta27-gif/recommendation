<?php

namespace App\Producer;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Producer
{
    private AMQPStreamConnection $connection;
    
    public function __construct(
        private readonly string $hostRabbit,
        private readonly int $portRabbit,
        private readonly string $userRabbit,
        private readonly string $passwordRabbit,
    )
    {
        $this->connection = new AMQPStreamConnection(
            $this->hostRabbit,
            $this->portRabbit,
            $this->userRabbit,
            $this->passwordRabbit,
        );
    }
    
    public function sendQueue(string $message, string $queueName): void
    {
        $channel = $this->connection->channel();

        $msg = new AMQPMessage(
            $message,
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT],
        );
        $channel->basic_publish($msg, '', $queueName);

        $channel->close();
        
        $this->connection->close();
    }
    
    public function sendExchange(string $message, string $exchange): void
    {
        $channel = $this->connection->channel();

        $msg = new AMQPMessage(
            $message,
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT],
        );
        $channel->basic_publish($msg, $exchange);

        $channel->close();
        
        $this->connection->close();
    }
}