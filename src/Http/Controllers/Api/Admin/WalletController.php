<?php

namespace Vtlabs\Core\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Depsimon\Wallet\Transaction;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Core\Http\Resources\Admin\TransactionAdminResource;

class WalletController extends Controller
{
    public function transactions(Request $request)
    {
        $request->validate([
            'type' => 'sometimes'
        ]);

        $transactions = Transaction::query();

        if($request->type) {
            $transactions = $transactions->where('type', $request->type);
        }

        return TransactionAdminResource::collection($transactions->latest()->paginate());
    }

    public function showTransaction(Transaction $transaction, Request $request)
    {
        return new TransactionAdminResource($transaction);
    }

    public function updateTransaction(Transaction $transaction, Request $request)
    {
        $request->validate([
            'is_paid' => 'required|boolean'
        ]);

        $meta = $transaction->meta ?? [];
        $meta['is_paid'] = $request->is_paid;

        $transaction->meta = $meta;

        $transaction->save();

        return new TransactionAdminResource($transaction);
    }

    public function rejectTransaction(Transaction $transaction, Request $request)
    {
        $request->validate([
            'is_reject' => 'required|boolean'
        ]);

        $meta = $transaction->meta ?? [];
        $meta['is_reject'] = $request->is_reject;

        $transaction->meta = $meta;

        $transaction->save();

        return new TransactionAdminResource($transaction);
    }
}
