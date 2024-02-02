<?php

namespace Vtlabs\Core\Listeners;

use Vtlabs\Core\Models\User\User;
use Vtlabs\Core\Events\Registered;
use Rennokki\Plans\Models\PlanModel;
use Vtlabs\Core\Events\RoleAssigned;
use Vtlabs\Payment\Events\PaymentUpdated;

class PaymentUpdatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Registered $event
     * @return void
     */
    public function handle(PaymentUpdated $event)
    {
        $payment = $event->payment;
        
        // if payable_type is Transaction we consider it as wallet payment and deposit the amount in user's wallet
        if ($payment->payable_type == 'Vtlabs\Core\Models\Transaction' && $payment->status == 'paid') {
            $user_id = $payment->payer_id;
            User::find($user_id)->deposit(
                $payment->amount,
                'deposit',
                [
                    'description' => 'Amount deposited in wallet',
                    'type' => 'deposit'
                ]
            );
        }

        // we need to subscribe plan according to payment status
        // mostly every package handle's it's own payment and plan assignment
        // this implementation was especially done in case of aztrading client where we are not using any other package
        if ($payment->payable_type == 'Rennokki\Plans\Models\PlanModel' && $payment->payer_type == User::class) {
            if (User::where('id', $payment->payer_id)->exists()) {
                if ($payment->status == 'paid') {
                    $plan = PlanModel::find($payment->payable_id);
                    $user = User::find($payment->payer_id)->first();
                    if ($user->hasActiveSubscription()) {
                        $user->cancelCurrentSubscription();
                    }
                    $user->subscribeTo($plan, $plan->duration);
                }
            }
        }
        return true;
    }
}
