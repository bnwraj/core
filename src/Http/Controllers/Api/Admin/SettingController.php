<?php

namespace Vtlabs\Core\Http\Controllers\Api\Admin;

use Vtlabs\Core\Models\Setting;
use Vtlabs\Core\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Brotzka\DotenvEditor\Exceptions\DotEnvException;
use Brotzka\DotenvEditor\DotenvEditor as Env;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Setting::all());
    }

    public function update(Request $request)
    {
        $inputs = $request->all();

        foreach ($inputs as $key => $value) {
            try {
                $setting = Setting::where('key', $key)->firstOrFail();
                $setting->value = $value;
                $setting->save();
            } catch (ModelNotFoundException $ex) {
                //
            }
        }

        return response()->json([]);
    }

    public function envList(Request $request)
    {
        $env = new Env();
        return response()->json($env->getContent());
    }

    /**
     * Update env variables.
     *
     * @param Request $request
     * @return mixed
     */
    public function updateEnv(Request $request)
    {
        $env = new Env();
        try {
            $env->changeEnv($request->all());
        } catch (DotEnvException $e) {
        }

        return response()->json([]);
    }
}