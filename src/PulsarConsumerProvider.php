<?php

namespace Linxi\PulsarClient;

use Illuminate\Support\ServiceProvider;

class PulsarConsumerProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('pulsar.consumer', function ($app) {
            return new PulsarConsumer($app['config']);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/pulsar.php' => config_path('pulsar.php')
        ]);

    }
}
