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
        $hasFiles = array_get($this->config, "$name.has_files", false);
        $casts    = array_get($this->config, "$name.casts", []);
        $defaults = array_get($this->config, "$name.defaults", []);

        // Prepare API request
        $params = $preparer->cast($casts, $params);
        $params = $preparer->setDefaults($defaults, $params);

        return $this->post($url, $params, $hasFiles);
    }

    protected function post(string $url, array $params, bool $withFiles = false)
    {
        $app    = array_get($this->config, 'app');
        $ticket = array_get($this->config, 'ticket');
        $cache  = array_get($this->config, 'cache');

        if (!$withFiles) {
            $apiCache = new ApiCache($cache ?: '');
            $key      = md5($app . $url . serialize($params));

            if ($response = $apiCache->get($key)) {
                return $response;
            }
        }

        $response = $this->proxy->{$withFiles ? 'postWithFiles' : 'post'}($url, array_merge($params, [
            'app'   => $app,
            'time'  => $time = time(),
            'token' => $this->calculateToken($app, $ticket, $time),
        ]));

        if (!$withFiles) {
            $apiCache->smartCache($key, $response);
        }

        return $response;
    }

    protected function calculateToken($app, $ticket, $time)
    {
        return md5($app . $time . $ticket);
    }
}
