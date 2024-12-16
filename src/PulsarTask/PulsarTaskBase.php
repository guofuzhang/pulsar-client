<?php

namespace Linxi\PulsarClient\PulsarTask;

use Pulsar\Message;

abstract class PulsarTaskBase
{
    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): object
    {
        if (!self::$instance instanceof static) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    abstract public function handdle(Message $message);

    private function __clone()
    {
    }

}
