<?php

namespace App\PulsarTask;

use App\PulsarTask\PulsarTaskBase;
use Pulsar\Message;

class PulsarTaskDemo extends PulsarTaskBase
{
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
