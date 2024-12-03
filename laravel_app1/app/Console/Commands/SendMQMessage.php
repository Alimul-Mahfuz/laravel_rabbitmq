<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class SendMQMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:trigger-direct';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger a direct exchange in RabbitMQ';

    /**
     * Execute the console command.
     * @throws Exception
     */
    public function handle(): void
    {
        $host = env('RABBITMQ_HOST', '127.0.0.1');
        $port = env('RABBITMQ_PORT', 5672);
        $user = env('RABBITMQ_USER', 'guest');
        $password = env('RABBITMQ_PASSWORD', 'guest');
        $vhost = env('RABBITMQ_VHOST', '/');
        $exchangeName = env('RABBITMQ_EXCHANGE', 'my_direct_exchange');
        $routingKey = env('RABBITMQ_ROUTING_KEY', 'my_routing_key');
        $messageBody = json_encode(['message' => 'Hello from Artisan Command!']);

        // Establish RabbitMQ connection
        $connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
        $channel = $connection->channel();

        // Declare the exchange
        $channel->exchange_declare($exchangeName, 'direct', false, true, false);

        // Publish the message
        $msg = new AMQPMessage($messageBody, ['content_type' => 'application/json']);
        $channel->basic_publish($msg, $exchangeName, $routingKey);

        $this->info("Message sent to direct exchange '{$exchangeName}' with routing key '{$routingKey}'.");

        // Close the connection
        $channel->close();
        $connection->close();
    }
}
