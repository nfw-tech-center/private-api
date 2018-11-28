<?php

namespace Abel\PrivateApi;

class PrivateApi
{
    public static function app($name)
    {
        return new Repository($name);
    }
}
