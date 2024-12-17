<?php

namespace Linxi\PulsarClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @version V1.0
 * @method static setTopicServer($topicServer,$deadLetter)
 * @method static sendAsync(string $payload, callable $callable, array $options = [])
 */
class PulsarProducerFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'pulsar_producer';
    }

    /**
     * @throws \Exception
     */
    public static function send($payload, array $options = [])
    {
        if (is_array($payload)){
            $payload = json_encode($payload);
        }

        if (!is_string($payload)){
            throw new \Exception('payload must be string or array');
        }

        return self::$app['pulsar_producer']->send($payload, $options);
    }
}
