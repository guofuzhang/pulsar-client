<?php

namespace Linxi\PulsarClient;

use Illuminate\Config\Repository as Config;
use InvalidArgumentException;
use Pulsar\Authentication\Jwt;
use Pulsar\Consumer;
use Pulsar\ConsumerOptions;
use Pulsar\Exception\IOException;
use Pulsar\Exception\OptionsException;
use Pulsar\Proto\CommandSubscribe\InitialPosition;

class PulsarConsumer
{
    protected $config;
    protected $consumers;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @throws OptionsException
     * @throws IOException
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->setTopicServer(), $method], $parameters);
    }

    /**
     * Desc:返回一个消费者连接
     * User: zhangguofu@douyuxingchen.com
     * Date: 2024/12/17 14:33
     * @param string $connectionName 连接名
     * @param string $subscriber 订阅者
     * @param bool $isDeadLetter 是否指定死信队列
     * @return mixed|Consumer
     * @throws IOException
     * @throws OptionsException
     */
    public function setTopicServer( $connectionName = 'default', $subscriber='', $isDeadLetter=false)
    {
        if (!$this->config->get("pulsar.topic_servers.$connectionName")) {
            throw new InvalidArgumentException("Invalid Pulsar connection: $connectionName");
        }
        if (!empty($this->consumers[$connectionName])) {
            return $this->consumers[$connectionName];
        }
        $topicConfig = $this->config->get("pulsar.topic_servers.$connectionName");

        $serverOption = $this->config->get("pulsar.connections.{$topicConfig['connection']}");
        $topicOption = $this->config->get("pulsar.topics.{$topicConfig['topic']}");

        if (!empty($subscriber)){
            $topicOption['subscriber']=$subscriber;
        }

        $option = new ConsumerOptions();
        $option->setConnectTimeout($serverOption['timeout']);
        $option->setAuthentication(new Jwt($serverOption['token']));

        $topicName=$topicOption['name'];
        $deadTopicName=$topicName.'_dead';

        if ($isDeadLetter){
            $topicName=$deadTopicName;
        }

        $option->setTopic($topicName);
        $option->setSubscription($topicOption['subscriber']);

        $option->setSubscriptionType($topicOption['subscription_type']);
        $option->setSubscriptionInitialPosition(InitialPosition::valueOf($topicOption['initial_position']));
        $option->setReceiveQueueSize($topicOption['queue_size']);
        if (!empty($topicOption['nack_redelivery_delay'])) {
            $option->setNackRedeliveryDelay($topicOption['nack_redelivery_delay']);
        }

        //死信队列配置
        if (!empty($topicOption['dead_letter_policy']) && empty($isDeadLetter)) {
            $option->setDeadLetterPolicy($topicOption['dead_letter_policy']['max_redeliver_count'], $deadTopicName, 'default');
        }
        $consumer = new Consumer($serverOption['url'], $option);
        $consumer->connect();
        $this->consumers[$connectionName] = $consumer;
        return $consumer;
    }

}
