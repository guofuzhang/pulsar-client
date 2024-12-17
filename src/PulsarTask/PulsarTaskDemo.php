<?php

namespace App\PulsarTask;

use Linxi\PulsarClient\PulsarTask\PulsarTaskBase;
use Pulsar\Message;

class PulsarTaskDemo extends PulsarTaskBase
{
    /**
     * Desc:处理消息的逻辑
     * User: zhangguofu@douyuxingchen.com
     * Date: 2024/12/17 14:40
     * @param Message $message
     * @return bool
     */
    public function handdle(Message $message)
    {
        echo sprintf('Got message 【%s】messageID[%s] topic[%s] nowTime[%s] publishTime[%s] redeliveryCount[%d]',
                $message->getPayload(),
                $message->getMessageId(),
                $message->getTopic(),
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s', $message->getPublishTime() / 1000),
                $message->getRedeliveryCount()
            ) . "\n";

        return true;
    }

}
