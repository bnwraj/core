<?php

namespace Vtlabs\Core\Listeners;

use Rennokki\Plans\Models\PlanModel;
use Vtlabs\Core\Models\User\User;
use Vtlabs\Payment\Events\OnWalletPayment;

class OnWalletPaymentListener
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
     * @param  OnWalletPayment $event
     * @return void
     */
    public function handle(OnWalletPayment $event)
    {
        $payment = $event->payment;

        if ($payment->payable_type == 'Rennokki\Plans\Models\PlanModel' && $payment->payer_type == User::class && $payment->status == 'paid') {
            $plan = PlanModel::find($payment->payable_id);

            $user = User::find($payment->payer_id);

            $user->withdraw($payment->amount, 'withdraw', [
                'description' => 'Bought Plan ' . $plan->name,
                'type' => 'purchase_plan'
            ]);
        }
    }
}
