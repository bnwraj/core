<?php

namespace Vtlabs\Core\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class MobileLanguage extends Model
{
    use Filterable;

    protected $table = 'mobile_languages';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'content' => 'json'
    ];
}