<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;


/**
 * @group Transaction Management
 *
 * APIs for handling user transfers
 */

class TransactionController extends Controller
{
    /**
     * View transaction status.
     *
     * @group Transaction
     * @authenticated
     * @urlParam id integer required The ID of the transaction.
     */

    public function show($id)
    {
        $transaction = Transaction::select('transactions.*', 'transfers.amount')
            ->join('transfers', 'transfers.id', 'transactions.transfer_id')
            ->where('transactions.id', $id)
            // ->where('transfers.user_id', auth()->id())
            ->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return response()->json([
            'amount' => $transaction->amount,
            'status' => $transaction->status_label, // Uses the accessor
            'timestamp' => $transaction->created_at->toDateTimeString(),
        ]);
    }
}
