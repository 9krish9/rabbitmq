<?php

require dirname(__DIR__). '/vendor/autoload.php';

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

$connection = new AMQPStreamConnection(
    $host,
    $port, 
    $user, 
    $pass, 
    $vhost,
    false,
    'AMQPLAIN',
    null,
    'en_US',
    3.0,
    120.0,
    null,
    true,
    60.0
);
$channel = $connection->channel();

$channel->queue_declare($queue, false, true, false, false);

$channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

$channel->queue_bind($queue, $exchange);

function process_message(AMQPMessage $message)
{
    //echo $message->body;
    $messageBody = json_decode($message->body);

    $email = $messageBody->email;

    // mail($email, $email, ' Subscribed ', $email. ' has subscribed to your channel' . PHP_EOL . $message->body);

    file_put_contents(dirname(__DIR__). '/data/' .$email. '.json', $message->body);
    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);

    // if ($message->body === 'quit') {
    //     $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
    // }
}

$consumerTag = 'local.krishdell.consumer';

$channel->basic_consume($queue, $consumerTag, false, false, false, false, 'process_message');

function shutdown($channel, $connection)
{
    $channel->close();
    $connection->close();
}

register_shutdown_function('shutdown', $channel, $connection);

while ($channel ->is_consuming()) {
    $channel->wait();
} 