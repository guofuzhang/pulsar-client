<?php

namespace Linxi\PulsarClient;

use Illuminate\Config\Repository as Config;
use InvalidArgumentException;
use Pulsar\Authentication\Jwt;
use Pulsar\Exception\IOException;
use Pulsar\Exception\OptionsException;
use Pulsar\Exception\RuntimeException;
use Pulsar\Producer;
use Pulsar\ProducerOptions;

class PulsarProducer
{
    protected $config;
    protected $producers;//connections

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @throws OptionsException
     * @throws IOException
     * @throws RuntimeException
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->setTopicServer(), $method], $parameters);
    }


    /**
     * Desc:返回一个生产者连接
     * User: zhangguofu@douyuxingchen.com
     * Date: 2024/12/17 14:37
     * @param string $connectionName
     * @return mixed|Producer
     * @throws IOException
     * @throws OptionsException
     * @throws RuntimeException
     */
    public function setTopicServer(string $connectionName = 'default')
    {
        if (!$this->config->get("pulsar.topic_servers.$connectionName")) {
            throw new InvalidArgumentException("Invalid Pulsar connection: $connectionName");
        }

        if (!empty($this->producers[$connectionName])) {
            return $this->producers[$connectionName];
        }

        $topicConfig = $this->config->get("pulsar.topic_servers.$connectionName");
        $serverOption = $this->config->get("pulsar.connections.{$topicConfig['connection']}");
        $topicOption = $this->config->get("pulsar.topics.{$topicConfig['topic']}");

        $option = new ProducerOptions();
        $option->setConnectTimeout($serverOption['timeout']);
        $option->setAuthentication(new Jwt($serverOption['token']));
        $option->setTopic($topicOption['name']);
        $producer = new Producer($serverOption['url'], $option);
        $producer->connect();
        $this->producers[$connectionName] = $producer;
        return $producer;
    }
    protected function __clone()
    {

    }


}
