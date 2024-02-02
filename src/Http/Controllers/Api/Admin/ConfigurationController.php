<?php

namespace Vtlabs\Core\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Vtlabs\Core\Http\Controllers\Controller;
use Brotzka\DotenvEditor\DotenvEditor as Env;

class ConfigurationController extends Controller
{
    public function languages(Request $request)
    {
        $env = new Env();
        return response()->json(explode(',', $env->getValue('APP_LOCALES')));   
    }
}
