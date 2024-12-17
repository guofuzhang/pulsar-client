<?php


return [
    'connections' => [
        'default' => [
            'url' => env('PULSAR_SERVICE_URL_DEFAULT', 'pulsar://localhost:6650'),
            'token' => env('PULSAR_SERVICE_TOKEN_DEFAULT', 'pulsar://localhost:6650'),
            'timeout' => env('PULSAR_SERVICE_TIMEOUT_DEFAULT', 3),
        ],
    ],

    'topics' => [
        'default' => [
            'name' => env('PULSAR_TOPIC_DEFAULT', 'persistent://public/test/demo'),
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
            ],
        ],
    ],

    'topic_servers' => [
        'default' => [
            'connection' => 'default',
            'topic' => 'default'
        ],
    ],
    'tasks' => [
        //task_name
        'pulsar_task_demo' => [
            'topic_server' => 'default',
            'task_process_class' => \App\PulsarTask\PulsarTaskDemo::class,
        ]
    ]

];
