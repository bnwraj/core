<?php

namespace Vtlabs\Core\Providers;

use Vtlabs\Core\Events\Registered;
use Vtlabs\Payment\Events\PaymentUpdated;
use Vtlabs\Core\Listeners\RegisteredListener;
use Vtlabs\Core\Listeners\PaymentUpdatedListener;
use Vtlabs\Core\Listeners\OnWalletPaymentListener;
use Vtlabs\Payment\Events\OnWalletPayment;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [RegisteredListener::class],
        PaymentUpdated::class => [PaymentUpdatedListener::class],
        OnWalletPayment::class => [OnWalletPaymentListener::class],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
