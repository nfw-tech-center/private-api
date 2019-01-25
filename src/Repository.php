<?php

namespace SouthCN\PrivateApi;

use AbelHalo\ApiProxy\ApiProxy;
use SouthCN\PrivateApi\Repositories\ApiCache;

class Repository
{
    protected $config;
    protected $proxy;

    public function __construct(string $app)
    {
        $this->config = config("private-api.$app");
        $this->proxy  = (new ApiProxy)
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
        $url      = array_get($this->config, "$name.url");
        $casts    = array_get($this->config, "$name.casts", []);
        $defaults = array_get($this->config, "$name.defaults", []);

        // Prepare API request
        $params = $preparer->cast($casts, $params);
        $params = $preparer->setDefaults($defaults, $params);

        return $this->post($url, $params);
    }

    protected function post(string $url, array $params)
    {
        $app    = array_get($this->config, 'app');
        $ticket = array_get($this->config, 'ticket');
        $cache  = array_get($this->config, 'cache');

        $apiCache = new ApiCache($cache ?: '');
        $key      = md5($app . $url . serialize($params));

        if ($response = $apiCache->get($key)) {
            return $response;
        }

        $response = $this->proxy->post($url, array_merge($params, [
            'app'   => $app,
            'time'  => $time = time(),
            'token' => $this->calculateToken($app, $ticket, $time),
        ]));

        $apiCache->smartCache($key, $response);

        return $response;
    }

    protected function calculateToken($app, $ticket, $time)
    {
        return md5($app . $time . $ticket);
    }
}
