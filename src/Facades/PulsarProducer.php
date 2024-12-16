<?php

namespace Linxi\PulsarClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @version V1.0
 * @method static setTopicServer($topicServer)
 * @method static send($payload, array $options = [])
 * @method static sendAsync(string $payload, callable $callable, array $options = [])
 */
class PulsarProducer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'pulsar.producer';
    }
}
