<?php

namespace Vtlabs\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

    protected $table = 'settings';

    protected $fillable = ['value'];

    public $timestamps = false;

    public static function findByKey($key)
    {
        return Setting::where('key', $key)->firstOrFail()->value;
    }
}
