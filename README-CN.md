# PHP Native Pulsar Client

# Contents

* [目录](#目录)
    * [关于](#关于)
    * [依赖](#依赖)
    * [安装](#安装)
    * [注册](#注册)
    * [发布引导文件](#发布引导文件)
    * [配置文件](#配置文件)
    * [生产者](#生产者)
    * [消费者](#消费者)
    * [可选配置项](#可选配置项)
    * [License](#License)

## 关于

这是一个用php实现的[Apache Pulsar](https://pulsar.apache.org)客户端库，基于[PulsarApi.proto](src/PulsarApi.proto)
，且支持Swoole协程环境

功能

- Support URL (`pulsar://` 、 `pulsar+ssl://` 、 `http://` 、 `https://`)
- Multi topic consumers
- TLS connection
- Automatic reconnection (Only Consumer)
- Message batching
- Message Properties
- Compression with `zstd`, `zlib`
- Authentication with `jwt`, `basic`
## Requirements

* PHP >=7.0 (Supported PHP8)
* ZLib Extension（如果你想使用`zlib`压缩）
* Zstd Extension（如果你想使用`zstd`压缩）

## 安装

```bash
composer require linxi/pulsar-client
```

## 注册
在config/app.php中注册provider和facade
```php
 'providers' => [
        PulsarProducerProvider::class,
        PulsarConsumerProvider::class,],
 'aliases' => [
        'PulsarProducer' => PulsarProducer::class,
        'PulsarConsumer' => PulsarConsumer::class,
]
```


## 发布引导文件
This command will generate a pulsar.php configuration file in the config directory. This file contains the configuration settings for the Pulsar client.
```php
php artisan vendor:publish --provider="Linxi\PulsarClient\PulsarProducerProvider"
```

### 配置文件
文件目录:config/pulsar.php
默认是default选项
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

### 消费者任务
请参考 pulsar task的demo文件 `App\PulsarTask\PulsarTaskDemo`
在Console\Kernel文件注册类
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

## 生产者

```php
<?php
use Linxi\PulsarClient\Facades\PulsarProducerFacade;
require_once __DIR__ . '/vendor/autoload.php';

//            $msgId = PulsarProducer::setTopicServer("default")->send("this message is " . time());
            $msgId = PulsarProducerFacade::send("this message is " . time());
```

## 消费者

```php
<?php
use Linxi\PulsarClient\Facades\PulsarConsumerFacade;
require_once __DIR__ . '/vendor/autoload.php';
        while (true) {
            $message = PulsarConsumerFacade::setTopicServer('default')->receive();
            echo sprintf('Got message 【%s】messageID[%s] topic[%s] nowTime[%s] publishTime[%s] redeliveryCount[%d]',
                    $message->getPayload(),
                    $message->getMessageId(),
                    $message->getTopic(),
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s', $message->getPublishTime() / 1000),
                    $message->getRedeliveryCount()
                ) . "\n";
            PulsarConsumerFacade::ack($message);
        }
```
## 配置消费任务
配置模块 config/pulsar.php
```php
    'tasks' => [
        //task_name
        'pulsar_task_demo' => [
            'topic_server' => 'default',
            'task_process_class' => App\PulsarTask\PulsarTaskDemo::class,
        ]
    ]
```

### 启动任务
```php
php artisan  pulsar:consumer pulsar_task_demo
```
## 命名规范
### 命名空间
- 命名空间是 TDMQ Pulsar 版中的一个资源管理概念。用户不同的业务场景一般都可以通过命名空间做隔离，并且针对不同的业务场景设置专门的配置，例如消息保留时间。不同命名空间之间的 Topic 相互隔离，订阅相互隔离，角色权限相互隔离。用户依赖于角色的权限(token)来控制访问命名空间. 
- 命名规范：以业务线为前缀，拼接处理业务 例如订单履约：`order_fulfillment`
### topic
- Topic 是 TDMQ Pulsar 版中的核心概念。Topic 通常用来对系统生产的各类消息做一个集中的分类和管理，例如和交易的相关消息可以放在一个名为 “trade” 的 Topic 中，供其他消费者订阅。
在实际应用场景中，一个 Topic 往往代表着一个业务聚合，由开发者根据自身系统设计、数据架构设计来决定如何设计不同的 Topic。
- 命名规范:具体业务线+业务动作,比如订单履约中的 课程授权，则命名规范为 `course_authorization_grant` 取消课程授权，则命名规范为 `course_authorization_cancel`

## 配置可选项

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
