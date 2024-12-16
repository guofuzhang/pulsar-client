# PHP Native Pulsar Client

# Contents

* [Contents](#Contents)
    * [About](#About)
    * [Requirements](#Requirements)
    * [Installation](#Installation)
    * [registering](#registering)
    * [Publish Vendor Files](#Publish Vendor Files)
    * [config/pulsar.php](#config/pulsar.php)
    * [Producer](#Producer)
    * [Consumer](#Consumer)
    * [Options](#Options)
    * [Options](#Options)
    * [License](#License)

## About

This is a [Apache Pulsar](https://pulsar.apache.org) client library implemented in php
Reference [PulsarApi.proto](src/PulsarApi.proto) And support Swoole coroutine

Features

- Support URL (`pulsar://` 、 `pulsar+ssl://` 、 `http://` 、 `https://`)
- Multi topic consumers
- TLS connection
- Automatic reconnection (Only Consumer)
- Message batching
- Message Properties
- Authentication with `jwt`, `basic`

## Requirements

* PHP >=7.0 (Supported PHP8)
* Swoole Extension(If you want to use in swoole)
    * Use in the swoole only requires that the `SWOOLE_HOOK_SOCKETS、SWOOLE_HOOK_STREAM_FUNCTION` or `SWOOLE_HOOK_ALL`

## Installation

```bash
composer require linxi/pulsar-client
```

## registering
registering service providers and facades in the config/app.php
```php
 'providers' => [
        PulsarProducerProvider::class,
        PulsarConsumerProvider::class,],
 'aliases' => [
        'PulsarProducer' => PulsarProducer::class,
        'PulsarConsumer' => PulsarConsumer::class,
]
```


## Publish Vendor Files
This command will generate a pulsar.php configuration file in the config directory. This file contains the configuration settings for the Pulsar client.
```php
php artisan vendor:publish --provider="Linxi\PulsarClient\PulsarProducerProvider"
```

### config/pulsar.php

the default option is default

```php 
<?php

return [

//config your pulsar connections 
    'connections' => [
        'default' => [
            'url' => env('PULSAR_SERVICE_URL_DEFAULT', 'pulsar://localhost:6650'),
            'token' => env('PULSAR_SERVICE_TOKEN_DEFAULT', 'pulsar://localhost:6650'),
            'timeout' => env('PULSAR_SERVICE_TIMEOUT_DEFAULT', 3),
        ],
    ],

//config your pulsar topics 
    'topics' => [
        'default' => [
            'name' => env('PULSAR_TOPIC_DEFAULT', 'persistent://public/test/demo_1216'),
            'subscriber' => env('PULSAR_SUBSCRIBER_DEFAULT', 'default_consumer'),
            //subscription_type Exclusive:0 Shared:1 Failover:2 Key_Shared:3
            'subscription_type' => env('PULSAR_SUBSCRIPTION_TYPE_DEFAULT', 1),
            //earliest:1  latest:0
            'initial_position' => env('PULSAR_SUBSCRIPTION_INITIAL_POSITION_DEFAULT', 1),
            'queue_size' => env('PULSAR_RECEIVE_QUEUE_SIZE', env('QUEUE_SIZE', 100)),
            //redeliver after a few seconds
            'nack_redelivery_delay' => env('PULSAR_SET_NACK_REDELIVERY_DELAY', 5),
            'dead_letter_policy' => [
                'max_redeliver_count' => env('PULSAR_MAX_REDELIVER_COUNT', 3),
                'dead_letter_topic' => env('PULSAR_DEAD_LETTER_TOPIC', 'persistent://public/test/test_dead')
            ],
        ],

    ],

//config your pulsar connetion and topic 

    'topic_servers' => [
        'default' => [
            'connection' => 'default',
            'topic' => 'default'
        ],
    ],
    
    //this is for consumer command 
    'tasks' => [
        //task_name
        'pulsar_test_task' => [
            'topic_server' => 'default',
            'task_process_class' => '\App\Pulsar\PulsarTest::class',
        ]
    ]

];


```

### PulsarTask
the demo of pulsar task is `App\PulsarTask\PulsarTaskDemo`

Register the Command Class
```php
use Linxi\PulsarClient\PulsarTask\PulsarConsumerTask;
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        PulsarConsumerTask::class,
    ];
}
```

## Producer

```php
<?php
use Linxi\PulsarClient\Facades\PulsarProducer;
require_once __DIR__ . '/vendor/autoload.php';

//            $msgId = PulsarProducer::setTopicServer("default")->send("this messge is " . time());
            $msgId = PulsarProducer::send("this messge is " . time());
```

## Consumer
```php
<?php
use Linxi\PulsarClient\Facades\PulsarConsumer;
require_once __DIR__ . '/vendor/autoload.php';
        while (true) {
            $message = PulsarConsumer::setTopicServer('default')->receive();
            echo sprintf('Got message 【%s】messageID[%s] topic[%s] nowTime[%s] publishTime[%s] redeliveryCount[%d]',
                    $message->getPayload(),
                    $message->getMessageId(),
                    $message->getTopic(),
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s', $message->getPublishTime() / 1000),
                    $message->getRedeliveryCount()
                ) . "\n";
            PulsarConsumer::ack($message);
        }
```
## Configure the Consumer Script
config tasks in config/pulsar.php
```php
    'tasks' => [
        //task_name
        'pulsar_task_demo' => [
            'topic_server' => 'default',
            'task_process_class' => App\PulsarTask\PulsarTaskDemo::class,
        ]
    ]
```

### Launch the Task
```php
php artisan  pulsar:consumer pulsar_task_demo
```

## Options

* Producer
    * setTopicServer()
    * send()
    * sendAsync()
* Consumer
    * receive()
    * batchReceive()
    * ack()
    * nack()
    * setTopicServer()

## MessageNotFound ErrCode （v1.2.1）

* `MessageNotFound::Ignore`
* `MessageNotFound::CommandParseFail`

## License

[MIT](LICENSE) LICENSE
