<?php

namespace SouthCN\PrivateApi;

use AbelHalo\ApiProxy\ApiProxy;
use SouthCN\PrivateApi\Repositories\ApiCache;
use SouthCN\PrivateApi\Repositories\AuthenticationAlgorithm;
use SouthCN\PrivateApi\Repositories\Preparer;

class Repository
{
    protected $proxy;
    protected $authAlgorithm;

    protected $app;
    protected $config;
    protected $guard;

    public function __construct(string $app, ?\Closure $guard = null)
    {
        $this->app    = $app;
        $this->config = config("private-api.$app");
        $this->guard  = $guard;

        $this->proxy = (new ApiProxy)
            ->headers(['Accept' => 'application/json'])
            ->setReturnAs(config('private-api._.return_type'));

        $this->proxy->logger->enable();

        $this->authAlgorithm = new AuthenticationAlgorithm($this->config['app'], $this->config['ticket']);
    }

    /**
     * @param  string  $name
     * @param  array   $params
     * @return mixed
     */
    public function api(string $name, array $params = [])
    {
        $this->guard($name);

        $preparer     = new Preparer;
        $url          = array_get($this->config, "$name.url");
        $hasFiles     = array_get($this->config, "$name.has_files", false);
        $casts        = array_get($this->config, "$name.casts", []);
        $defaults     = array_get($this->config, "$name.defaults", []);
        $parameterMap = array_get($this->config, "$name.parameter_map_of_app", []);
        $httpLogic    = array_get($this->config, "$name.custom_http_logic");

        // Prepare API request
        $params = $preparer->cast($casts, $params);
        $params = $preparer->setDefaults($defaults, $params);
        $params = $preparer->setParameterMap($parameterMap, $params);

        if ($httpLogic) {
            $wrapper = app($httpLogic);

            return $wrapper($this->proxy, $url, $params);
        }

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
            $key      = md5($this->authAlgorithm->app . $url . serialize($params));

            if ($response = $apiCache->get($key)) {
                return $response;
            }
        }
        $response = $this->proxy->{$withFiles ? 'postWithFiles' : 'post'}(
            $url,
            $this->authAlgorithm->processParams($params)
        );

        if (!$withFiles) {
            $apiCache->smartCache($key, $response);
        }

        return $response;
    }
}
