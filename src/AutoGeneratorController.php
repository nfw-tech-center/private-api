<?php

namespace Abel\PrivateApi;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class AutoGeneratorController extends Controller
{
    public function generateRoute(Request $request)
    {
        $privateApi = Cache::get('private-api:route:' . $request->route()->uri);

        return PrivateApi::app($privateApi['app'])->api($privateApi['api'], $request->all());
    }
}
