<?php

namespace Abel\PrivateApi;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config.php' => config_path('private-api.php'),
        ]);
    }
}
