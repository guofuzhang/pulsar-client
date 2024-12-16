<?php

namespace Linxi\PulsarClient;

use Illuminate\Config\Repository as Config;
use InvalidArgumentException;
use Pulsar\Authentication\Jwt;
use Pulsar\Consumer;
use Pulsar\ConsumerOptions;
use Pulsar\Proto\CommandSubscribe\InitialPosition;

class PulsarConsumer
{
    protected $config;
    protected $consumers;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->setTopicServer(), $method], $parameters);
    }

    public function setTopicServer($connectionName = 'default')
    {
        if (!empty($this->consumers[$connectionName])) {
            return $this->consumers[$connectionName];
        }
        if (!$this->config->get("pulsar.topic_servers.{$connectionName}")) {
            throw new InvalidArgumentException("Invalid Pulsar connection: {$connectionName}");
        }
        $topicConfig = $this->config->get("pulsar.topic_servers.{$connectionName}");

        $serverOption = $this->config->get("pulsar.connections.{$topicConfig['connection']}");
        $topicOption = $this->config->get("pulsar.topics.{$topicConfig['topic']}");

        $option = new ConsumerOptions();
        $option->setConnectTimeout($serverOption['timeout']);
        $option->setAuthentication(new Jwt($serverOption['token']));
        $option->setTopic($topicOption['name']);
        $option->setSubscription($topicOption['subscriber']);

        $option->setSubscriptionType($topicOption['subscription_type']);
        $option->setSubscriptionInitialPosition(InitialPosition::valueOf($topicOption['initial_position']));
        $option->setReceiveQueueSize($topicOption['queue_size']);
        if (!empty($topicOption['nack_redelivery_delay'])) {
            $option->setNackRedeliveryDelay($topicOption['nack_redelivery_delay']);
        }

        if (!empty($topicOption['dead_letter_policy'])) {
            $option->setDeadLetterPolicy($topicOption['dead_letter_policy']['max_redeliver_count'], $topicOption['dead_letter_policy']['dead_letter_topic'], 'default');
        }
        $consumer = new Consumer($serverOption['url'], $option);
        $consumer->connect();
        $this->consumers[$connectionName] = $consumer;
        return $consumer;
    }

}
