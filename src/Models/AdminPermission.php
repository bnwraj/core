<?php

namespace Vtlabs\Core\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPermission extends Model
{
    protected $table = 'admin_permissions';

    protected $fillable = ['role', 'permissions', 'meta'];

    protected $casts = [
        'meta' => 'array'
    ];
}
