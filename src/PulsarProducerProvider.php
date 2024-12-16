<?php

namespace Linxi\PulsarClient;

use Illuminate\Support\ServiceProvider;

class PulsarProducerProvider extends ServiceProvider
{
    protected $defer = true;
    public function register()
    {
        $this->app->singleton('pulsar.producer', function ($app) {
            return new PulsarProducer($app['config']);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/pulsar.php' => config_path('pulsar.php')
        ]);

    }
}
