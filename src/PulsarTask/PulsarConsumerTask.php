<?php

namespace Linxi\PulsarClient\PulsarTask;

use Illuminate\Console\Command;
use Linxi\PulsarClient\Facades\PulsarConsumer;

class PulsarConsumerTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pulsar:consumer {taskName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $taskName = $this->argument('taskName');
        $pulsarTaskConfig = config('pulsar.tasks');
        $taskConfig = $pulsarTaskConfig[$taskName];
        $consumer = PulsarConsumer::setTopicServer($taskConfig['topic_server']);
        while (true) {
            $message = $consumer->receive();
            $taskProcessClass = $taskConfig['task_process_class'];
            $res = $taskProcessClass::getInstance()->handdle($message);
            if ($res) {
                $consumer->ack($message);
            } else {
                $consumer->nack($message);
            }
        }
    }
}
