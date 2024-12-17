<?php

namespace Linxi\PulsarClient\Providers;

use Illuminate\Support\ServiceProvider;
use Linxi\PulsarClient\Facades\PulsarConsumerFacade;
use Linxi\PulsarClient\Facades\PulsarProducerFacade;
use Linxi\PulsarClient\PulsarConsumer;
use Linxi\PulsarClient\PulsarProducer;

class PulsarProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('pulsar_consumer', function ($app) {
            return new PulsarConsumer($app['config']);
        });
        $this->app->singleton('pulsar_producer', function ($app) {
            return new PulsarProducer($app['config']);
        });
        $this->app->alias('pulsar_consumer', PulsarConsumerFacade::class);
        $this->app->alias('pulsar_producer', PulsarProducerFacade::class);
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/pulsar.php' => base_path('config/pulsar.php'),
            __DIR__ . '/../PulsarTask/PulsarTaskDemo.php' => base_path('app/PulsarTask/PulsarTaskDemo.php'),
        ]);

    }
}
