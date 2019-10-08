<?php

namespace SouthCN\PrivateApi\Repositories;

use Carbon\Carbon;

class Preparer
{
    protected $casts;
    protected $defaults;
    protected $parameterMap;

    public function __construct(array $apiConfig)
    {
        $this->casts        = array_get($apiConfig, 'casts', []);
        $this->defaults     = array_get($apiConfig, 'defaults', []);
        $this->parameterMap = array_get($apiConfig, 'parameter_map_of_app', []);
    }

    public function cast(array $params): array
    {
        collect($this->casts)->each(function ($cast, $key) use (&$params) {
            if (!array_has($params, $key)) {
                return;
            }

            $value = $params[$key];
            [$from, $to] = explode(' -> ', $cast);

            if ('timestamp' == $from) {
                $value = Carbon::createFromTimestamp($value);
            }

            if ('datetime' == $to) {
                $value = $value->toDateTimeString();
            }

            $params[$key] = $value;
        });

        return $params;
    }

    public function setDefaults(array $params): array
    {
        collect($this->defaults)->each(function ($value, $key) use (&$params) {
            $params[$key] = array_get($params, $key, $value);
        });

        return $params;
    }

    public function setParameterMap(array $params): array
    {
        if (empty($this->parameterMap)) {
            return $params;
        }

        $pairs = collect($this->parameterMap)->get(request('app'));

        if (!$pairs) {
            abort(403, 'API鉴权错误：APP无此接口权限');
        }

        return array_merge($params, $pairs);
    }
}
