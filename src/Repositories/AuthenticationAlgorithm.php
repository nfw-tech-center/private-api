<?php

namespace SouthCN\PrivateApi\Repositories;

class AuthenticationAlgorithm
{
    public $app;
    public $ticket;

    public function __construct(string $app, string $ticket)
    {
        $this->app    = $app;
        $this->ticket = $ticket;
    }

    public function processParams(array $params): array
    {
        $time = time();

        $params['app']   = $this->app;
        $params['time']  = $time;
        $params['token'] = $this->calculateToken($this->app, $this->ticket, $time);

        return $params;
    }

    protected function calculateToken(string $app, string $ticket, string $time): string
    {
        return md5($app . $time . $ticket);
    }
}
