<?php

namespace SouthCN\PrivateApi;

use Carbon\Carbon;

class Preparer
{
    public function cast(array $casts, array $params): array
    {
        collect($casts)->each(function ($cast, $key) use (&$params) {
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

    public function setDefaults(array $defaults, array $params): array
    {
        collect($defaults)->each(function ($value, $key) use (&$params) {
            $params[$key] = array_get($params, $key, $value);
        });

        return $params;
    }

    public function setParameterMap(array $map, array $params, string $app): array
    {
        $pairs = collect($map)->get($app);

        if (!$pairs) {
            abort(403, 'API鉴权错误：APP无此接口权限');
        }

        return array_merge($params, $pairs);
    }
}
