<?php

namespace Abel\PrivateApi;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config.php' => config_path('private-api.php'),
        ]);

        foreach (config('private-api') as $appName => $appDefinition) {
            foreach ($appDefinition as $apiName => $apiDefinition) {
                if (!is_array($apiDefinition)) {
                    continue;
                }

                if (array_has($apiDefinition, 'route')) {
                    $route      = $apiDefinition['route'];
                    $privateApi = [
                        'app' => $appName,
                        'api' => $apiName,
                    ];

                    Cache::forever("private-api:route:$route", $privateApi);

                    Route::middleware('api')->any($route, AutoGeneratorController::class . '@generateRoute');
                }
            }
        }
    }
}
