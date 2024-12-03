<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class ConsumeRabbitMQ extends Command
{
    protected $signature = 'rabbitmq:consume';
    protected $description = 'Consume messages from RabbitMQ';

    public function handle()
    {
        $host = env('RABBITMQ_HOST', '127.0.0.1');
        $port = env('RABBITMQ_PORT', 5672);
        $user = env('RABBITMQ_USER', 'guest');
        $password = env('RABBITMQ_PASSWORD', 'guest');
        $vhost = env('RABBITMQ_VHOST', '/');
        $queueName = env('RABBITMQ_QUEUE', 'my_direct_queue');

        Log::info('Starting RabbitMQ consumer...');
        echo "Starting RabbitMQ consumer...\n";

        $connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
        $channel = $connection->channel();

        $channel->queue_declare($queueName, false, true, false, false);

        $callback = function ($msg) {
            echo "Message consumed: " . $msg->body . PHP_EOL; // Print to console
            Log::info('Message consumed: ', ['body' => $msg->body]); // Log message
            $msg->ack(); // Acknowledge the message
        };

        $channel->basic_consume($queueName, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();

        Log::info('RabbitMQ consumer stopped.');
        echo "RabbitMQ consumer stopped.\n";
    }
}
