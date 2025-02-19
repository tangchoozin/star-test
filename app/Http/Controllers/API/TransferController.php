<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transfer;
use App\Models\Transaction;
use App\Models\User;
use App\Jobs\ProcessTransfer;
use DB;

class TransferController extends Controller
{
    //
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id|different:user_id',
            'amount' => 'required|numeric|min:1',
            'idempotency_key' => 'required|string|unique:transfers,idempotency_key',
        ]);

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
                'idempotency_key' => $idempotencyKey
            ]);

            $transaction = Transaction::create([
                'transfer_id' => $transfer->id,
                'status' => 0
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
