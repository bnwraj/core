<?php

namespace Vtlabs\Core\Models;

use Vtlabs\Payment\Traits\CanBePaid;
use Vtlabs\Payment\Contracts\Payable;
use Illuminate\Database\Eloquent\Builder;
use Depsimon\Wallet\Transaction as WalletTransaction;

class Transaction extends WalletTransaction implements Payable
{
    use CanBePaid;

    protected static function boot()
    {
        parent::boot();

        // add a global scope to our model so that it only returns accepted transactions only
        static::addGlobalScope('accepted', function (Builder $builder) {
            $builder->where('accepted', true);
        });
    }
}
