<?php

namespace Linxi\PulsarClient\Facades;

use Illuminate\Support\Facades\Facade;
use Pulsar\Message;

/**
 * @method static receive(bool $loop = true): Message
 * @method static batchReceive(bool $loop = true): array
 * @method static ack(Message $message)
 * @method static nack(Message $message)
 * @method static setTopicServer($topicServer,)
 * @method static setTopic()
 */
class PulsarConsumerFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'pulsar_consumer';
    }
}
