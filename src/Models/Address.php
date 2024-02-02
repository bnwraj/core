<?php

namespace Vtlabs\Core\Models;

use Vtlabs\Core\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'addresses';

    /**
     * {@inheritdoc}
     */
    protected $guarded = [];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float',
        'meta' => 'json'
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
