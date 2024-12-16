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
    protected $producers;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->setTopicServer(), $method], $parameters);
    }

    /**
     * @param $connectionName
     * @return mixed|Producer
     * @throws IOException
     * @throws OptionsException
     * @throws RuntimeException
     */
    public function setTopicServer($connectionName = 'default')
    {
        if (!empty($this->producers[$connectionName])) {
            return $this->producers[$connectionName];
        }
        if (!$this->config->get("pulsar.topic_servers.{$connectionName}")) {
            throw new InvalidArgumentException("Invalid Pulsar connection: {$connectionName}");
        }

        $topicConfig = $this->config->get("pulsar.topic_servers.{$connectionName}");
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
