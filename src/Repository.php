<?php

namespace SouthCN\PrivateApi;

use AbelHalo\ApiProxy\ApiProxy;

class Repository
{
    protected $app;
    protected $proxy;

    public function __construct(string $app)
    {
        $this->app   = $app;
        $this->proxy = (new ApiProxy)
            ->enableLog()
            ->headers(['Accept' => 'application/json'])
            ->setReturnAs(config('private-api._.return_type'));
    }

    /**
     * @param string $name
     * @param array  $params
     * @return mixed
     */
    public function api(string $name, array $params = [])
    {
        $preparer = new Preparer;
        $app      = config("private-api.$this->app.app");
        $ticket   = config("private-api.$this->app.ticket");
        $url      = config("private-api.$this->app.$name.url");
        $casts    = config("private-api.$this->app.$name.casts");
        $defaults = config("private-api.$this->app.$name.defaults");

        // Prepare API request
        $params = $preparer->cast($casts, $params);
        $params = $preparer->setDefaults($defaults, $params);

        return $this->proxy->post($url, array_merge($params, [
            'app'   => $app,
            'time'  => $time = time(),
            'token' => $this->calculateToken($app, $ticket, $time),
        ]));
    }

    protected function calculateToken($app, $ticket, $time)
    {
        return md5($app . $time . $ticket);
    }
}
