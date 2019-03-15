<?php

namespace SouthCN\PrivateApi;

use AbelHalo\ApiProxy\ApiProxy;
use SouthCN\PrivateApi\Repositories\ApiCache;

class Repository
{
    protected $app;
    protected $config;
    protected $guard;
    protected $proxy;

    protected $clientApp;
    protected $clientTicket;

    public function __construct(string $app, ?\Closure $guard = null)
    {
        $this->app    = $app;
        $this->config = config("private-api.$app");
        $this->guard  = $guard;
        $this->proxy  = (new ApiProxy)
            ->enableLog()
            ->headers(['Accept' => 'application/json'])
            ->setReturnAs(config('private-api._.return_type'));

        $this->clientApp    = array_get($this->config, 'app');
        $this->clientTicket = array_get($this->config, 'ticket');
    }

    /**
     * @param string $name
     * @param array  $params
     * @return mixed
     */
    public function api(string $name, array $params = [])
    {
        $this->guard($name);

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

    protected function guard(string $name): void
    {
        if (!is_callable($this->guard)) {
            return;
        }

        if (!($this->guard)($this->app, $name)) {
            abort(403, '无此API授权');
        }
    }

    protected function post(string $url, array $params, bool $withFiles = false)
    {
        $cache = array_get($this->config, 'cache');

        if (!$withFiles) {
            $apiCache = new ApiCache($cache ?: '');
            $key      = md5($this->clientApp . $url . serialize($params));

            if ($response = $apiCache->get($key)) {
                return $response;
            }
        }

        $response = $this->proxy->{$withFiles ? 'postWithFiles' : 'post'}($url, array_merge($params, [
            'app'   => $this->clientApp,
            'time'  => $time = time(),
            'token' => $this->calculateToken($this->clientApp, $this->clientTicket, $time),
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
