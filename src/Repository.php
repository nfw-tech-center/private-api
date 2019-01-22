<?php

namespace SouthCN\PrivateApi;

use AbelHalo\ApiProxy\ApiProxy;
use Carbon\Carbon;

class Repository
{
    protected $app;
    protected $proxy;

    public function __construct($app)
    {
        $this->app   = $app;
        $this->proxy = (new ApiProxy)->setReturnAs(
            config('private-api._.return_type')
        );
    }

    /**
     * @return mixed
     */
    public function api($name, $params = [])
    {
        $app      = config("private-api.$this->app.app");
        $ticket   = config("private-api.$this->app.ticket");
        $url      = config("private-api.$this->app.$name.url");
        $casts    = config("private-api.$this->app.$name.casts");
        $defaults = config("private-api.$this->app.$name.defaults");

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

        collect($defaults)->each(function ($value, $key) use (&$params) {
            $params[$key] = array_get($params, $key, $value);
        });

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
