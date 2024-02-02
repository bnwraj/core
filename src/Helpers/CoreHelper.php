<?php

namespace Vtlabs\Core\Helpers;

use EloquentFilter\ModelFilter;
use Vtlabs\Core\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Storage;

class CoreHelper
{
    public static function transformRequestForTranslation($key, $input)
    {
        $translations = [];
        foreach ($input as $value) {
            $translations[$value['language']] = $value['text'];
        }
        request()->merge(["_" . $key => $translations]);
    }

    public static function rolesForValidation()
    {
        $roles = Role::all()->pluck('name')->filter(function ($v, $k) {
            return $v[0] != '_';
        });
        return $roles->implode(',');
    }

    public static function isValidLocale($locale)
    {
        return in_array($locale, explode(",", config('app.locales')));
    }

    public static function tempPath(UploadedFile $file)
    {
        return Storage::path(Storage::putFile('temp', $file));
    }

    public static function getFirebaseKeyIds()
    {
        $publicKeyURL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';
        if (!ini_get('allow_url_fopen')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $publicKeyURL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $publicKey = curl_exec($ch);
            curl_close($ch);
            $kids = json_decode($publicKey, true);
        } else {
            $kids = json_decode(file_get_contents($publicKeyURL), true);
        }
        return $kids;
    }

    public static function settingsAsDictionary()
    {
        $settings = [];
        foreach (Setting::all() as $setting) {
            $settings[$setting->key] = $setting->value;
        }
        return $settings;
    }

    public static function fillTranslation($model, $fields)
    {
        $locales = explode(",", config('app.locales'));
        foreach ($fields as $key => $value) {
            // only update translation if it's value is changed
            if($model->{$key} != $fields[$key]) {
                $translations = [];
                foreach ($locales as $locale) {
                    $translations[$locale] = $value;
                }   
                $model->setTranslations($key, $translations);
            }
        }
    }

    public static function dumpQueryLog($query)
    {
        DB::connection()->enableQueryLog();
        $query->get();
        dd( DB::getQueryLog());
    }

    public static function searchTranslated($filter, string $columnName, string $locale, string $value)
    {
        return $filter->where(DB::raw('lower(' . $columnName .')'), 'LIKE', '%' . strtolower($value) . '%');
    } 

    public static function isAdmin()
    {
        return (Auth::user() && Auth::user()->hasRole('admin'));
    }

    public static function generatePin($digits = 6)
    {
        $i = 0;
        $pin = "";

        while ($i < $digits) {
            $pin .= mt_rand(0, 9);
            $i++;
        }

        return $pin;
    }
}
