<?php

require dirname(__DIR__).'/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

$host = 'jaguar.rmq.cloudamqp.com';
$port = 5672;
$user = 'qhowxprm';
$pass = '6N0ijPrAVQgnfpK_fdHo084xrb7cO7TO';
$vhost = 'qhowxprm';

$exchange = 'subscribers';
$queue = 'krish_subscribers';

$connection = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
$channel = $connection->channel();

$channel->queue_declare($queue, false, true, false, false);

$channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

$channel->queue_bind($queue, $exchange);

$faker = Faker\Factory::create();

$limit = 20000;
$iteration = 0;

while($iteration < $limit) {

    $messageBody = json_encode([
        'name' => $faker->name,
        'email' => $faker->email,
        'address' => $faker->address,
        'subscribed' => true
    ]);
    $message = new AMQPMessage($messageBody, [
        'content_type' => 'application/json', 
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
    ]);
    $channel->basic_publish($message, $exchange);
    // echo 'Published message to queue: '. $queue. PHP_EOL;
    // var_dump($messageBody);
    $iteration++;
}

echo 'Finished publishing to queue: '. $queue. PHP_EOL;

$channel->close();
$connection->close();