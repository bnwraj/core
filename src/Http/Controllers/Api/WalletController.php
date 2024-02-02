<?php

namespace Vtlabs\Core\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vtlabs\Core\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Payment\Http\Resources\PaymentResource;
use Vtlabs\Core\Http\Resources\TransactionResource;

/**
 * @group  User Management
 *
 * APIs for user's wallet
 */
class WalletController extends Controller
{
    public function __construct()
    {
    }

    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'payment_method_slug' => 'required|exists:payment_methods,slug'
        ]);

        // create a non accepted deposit so that we can have a $transaction object which can be 
        // paid using payment module, once the payment is complete we will make the actual deposit
        // in the payment update listener
        Auth::user()->deposit($request->amount, 'deposit', [], false);
        $transaction = Auth::user()->transactions()->withoutGlobalScope('accepted')->latest()->first();

        $payment = Auth::user()->createPayment($transaction, $request->amount, $request->payment_method_slug);

        return new PaymentResource($payment);
    }

    public function checkBalance(Request $request)
    {
        return response()->json(["balance" => Auth::user()->balance]);
    }

    public function transactions(Request $request)
    {
        $request->validate([
            'type' => 'sometimes'
        ]);

        $transactions = Auth::user()->transactions();

        if ($request->type) {
            $transactions = $transactions->where('meta->type', $request->type);
        }

        return TransactionResource::collection($transactions->orderBy('created_at', 'desc')->paginate());
    }

    public function payout(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string',
            'bank_account_name' => 'required|string',
            'bank_account_number' => 'required|string',
            'bank_code' => 'sometimes',
            'amount' => 'required|numeric|max:' . Auth::user()->balance
        ]);

        Auth::user()->withdraw(
            $request->amount,
            'payout',
            [
                'description' => 'Payout',
                'type' => 'payout',
                'bank_name' => $request->bank_name,
                'bank_account_name' => $request->bank_account_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_code' => $request->bank_code,
            ]
        );

        return response()->json([], 200);
    }

    public function earningsSummary(Request $request)
    {
        $request->validate([
            'duration' => 'required|in:hours,days,months,years',
            'limit' => 'required|numeric'
        ]);

        $totalEarnings = Transaction::where('wallet_id', Auth::user()->wallet->id)->where('type', 'deposit')->whereDate('created_at', '<=', Carbon::now());

        if ($request->duration == 'hours') {
            $totalEarnings = $totalEarnings->whereDate('created_at', '>', Carbon::now()->subDays(1));

            $earningsChartData = Transaction::where('wallet_id', Auth::user()->wallet->id)->select(DB::raw('HOUR(created_at) as period'), DB::raw('sum(amount) as total'))
                ->whereDate('created_at', '>', Carbon::now()->subDays(1))
                ->whereDate('created_at', '<=', Carbon::now())
                ->where('type', 'deposit')
                ->groupBy(DB::raw('HOUR(created_at)'));
        }

        if ($request->duration == 'days') {
            $totalEarnings = $totalEarnings->whereDate('created_at', '>', Carbon::now()->subDays(7));

            $earningsChartData = Transaction::where('wallet_id', Auth::user()->wallet->id)->select(DB::raw('DATE(created_at) as period'), DB::raw('sum(amount) as total'))
                ->whereDate('created_at', '>', Carbon::now()->subDays($request->limit))
                ->whereDate('created_at', '<=', Carbon::now())
                ->where('type', 'deposit')
                ->groupBy(DB::raw('DATE(created_at)'));
        }

        if ($request->duration == 'months') {
            $totalEarnings = $totalEarnings->whereDate('created_at', '>', Carbon::now()->subDays(30));

            $earningsChartData = Transaction::where('wallet_id', Auth::user()->wallet->id)->select(DB::raw('MONTH(created_at) as period'), DB::raw('sum(amount) as total'))
                ->whereDate('created_at', '>', Carbon::now()->subDays($request->limit * 30))
                ->whereDate('created_at', '<=', Carbon::now())
                ->where('type', 'deposit')
                ->groupBy(DB::raw('MONTH(created_at)'));
        }

        if ($request->duration == 'years') {
            $totalEarnings = $totalEarnings->whereDate('created_at', '>', Carbon::now()->subDays(365));

            $earningsChartData = Transaction::where('wallet_id', Auth::user()->wallet->id)->select(DB::raw('YEAR(created_at) as period'), DB::raw('sum(amount) as total'))
                ->whereDate('created_at', '>', Carbon::now()->subDays($request->limit * 365))
                ->whereDate('created_at', '<=', Carbon::now())
                ->where('type', 'deposit')
                ->groupBy(DB::raw('YEAR(created_at)'));
        }

        return response()->json([
            'total_earnings' => $totalEarnings->sum('amount'),
            'earnings_chart_data' => $earningsChartData->get()
        ]);
    }
}
