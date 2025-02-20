<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessTransfer;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Validator;

/**
 * @group Transfer Management
 *
 * APIs for handling user transactions
 */

class TransferController extends Controller
{
    /**
     * Transfer money between users.
     *
     * @group Transfer
     * @authenticated
     *
     * @bodyParam receiver_id int required The recipient's user ID. Example: 2
     * @bodyParam amount float required The transfer amount. Minimum 1. Example: 100.50
     * @bodyParam idempotency_key string required A client site generated unique identifier (browser, mobile apps, backend generated) to prevent duplicate transactions. Example: "abc123"
     *
     * @response 200 {
     *   "transfer_id": 1,
     *   "message": "Transfer successful"
     * }
     * @response 422 {
     *   "message": "Validation failed",
     *   "errors": {
     *     "amount": "The amount must be at least 1."
     *   }
     * }
     */

    public function store(Request $request)
    {
        $rules = [
            'receiver_id' => [
                'required',
                'exists:users,id',
                'not_in:' . auth()->id(), // Ensure receiver is not the same as the sender
            ],
            'amount' => 'required|numeric|min:1',
            'idempotency_key' => 'required|string|unique:transfers,idempotency_key',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed','errors' => $validator->errors()], 200);
        }

        $sender = auth()->user();
        $receiver = User::find($request->receiver_id);
        $amount = $request->amount;
        $idempotencyKey = $request->idempotency_key;

        // Balance validation
        if ($sender->balance < $amount) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        DB::beginTransaction();
        try {
            // Store transfer
            $transfer = Transfer::create([
                'user_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'idempotency_key' => $idempotencyKey,
            ]);

            $transaction = Transaction::create([
                'transfer_id' => $transfer->id,
                'status' => 0,
            ]);

            ProcessTransfer::dispatch($transfer);

            DB::commit();

            return response()->json([
                'transfer_id' => $transfer->id,
                'message' => 'Transfer successful',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Transfer failed. '.$e->getMessage()], 500);
        }
    }
}
