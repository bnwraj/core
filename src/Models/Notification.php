<?php

namespace Vtlabs\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $guarded = [];

    protected $casts = [
        'meta' => 'json',
        'is_read' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.models.user'));
    }

    public function fromuser()
    {
        return $this->belongsTo(config('auth.models.user'), 'from_user_id');
    }
}