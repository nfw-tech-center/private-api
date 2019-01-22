<?php

namespace SouthCN\PrivateApi;

class PrivateApi
{
    public static function app($name)
    {
        return new Repository($name);
    }
}
