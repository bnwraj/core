<?php

namespace Vtlabs\Core\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{

    protected $table = 'activity_logs';

    protected $fillable = ['device_id', 'device_type', 'user_id'];
}
