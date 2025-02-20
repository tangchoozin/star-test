<?php

namespace App\Jobs;

use App\Models\Transfer;
use App\Models\TransferLog;
use App\Models\User;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ProcessTransfer implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $transfer;

    /**
     * Create a new job instance.
     */
    public function __construct(Transfer $transfer)
    {
        //
        $this->transfer = $transfer;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        Log::info("Processing transfer ID: {$this->transfer->id}");

        $transfer = Transfer::find($this->transfer->id);
        $transaction = $transfer->transaction;

        if (empty($transfer) || empty($transaction)) {
            $transaction->update(['status' => 20]); // Failed
            Log::warning("Transfer ID #{$this->transfer->id} not found.");

            TransferLog::create([
                'transfer_id' => $this->transfer->id,
                'status' => 'failed',
                'message' => 'Transfer not found.',
            ]);
            return;
        }

        if ($transaction->status !== 0) {
            Log::warning("Transfer ID #{$this->transfer->id} already processed.");
            TransferLog::create([
                'transfer_id' => $this->transfer->id,
                'status' => 'failed',
                'message' => 'Transfer already processed.',
            ]);
            return;
        }

        TransferLog::create([
            'transfer_id' => $transfer->id,
            'status' => 'processing',
            'message' => 'Transfer is being processed.',
        ]);

        $sender = User::find($transfer->user_id);
        $receiver = User::find($transfer->receiver_id);

        if (!$sender || !$receiver) {
            Log::error("Transfer ID #{$this->transfer->id} sender or receiver not found");

            TransferLog::create([
                'transfer_id' => $transfer->id,
                'status' => 'failed',
                'message' => 'Sender or receiver not found.',
            ]);

            $transaction->update(['status' => 20]); // Failed
            return;
        }

        if ($sender->balance < $transfer->amount) {
            Log::error("Transfer ID #{$this->transfer->id} failed. Insufficient balance for sender ID: {$sender->id}");

            TransferLog::create([
                'transfer_id' => $transfer->id,
                'status' => 'failed',
                'message' => 'Insufficient balance.',
            ]);

            $transaction->update(['status' => 20]); // Failed
            return;
        }

        DB::beginTransaction();
        try {
            // Deduct balance from sender
            $sender->balance -= $transfer->amount;
            $sender->save();

            // Add balance to receiver
            $receiver->balance += $transfer->amount;
            $receiver->save();

            // Update transfer status to completed
            $transaction->update(['status' => 10]); // Completed

            TransferLog::create([
                'transfer_id' => $transfer->id,
                'status' => 'completed',
                'message' => 'Transfer completed successfully.',
            ]);

            DB::commit();
            Log::info("Transfer ID: {$transfer->id} successfully processed.");
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Transfer ID: {$transfer->id} failed. Error: {$e->getMessage()}");

            TransferLog::create([
                'transfer_id' => $transfer->id,
                'status' => 'retry',
                'message' => "Transfer failed. Retrying... Error: {$e->getMessage()}",
            ]);

            // Retry the job automatically
            $this->release(10); // Retry after 10 seconds
        }
    }
}
