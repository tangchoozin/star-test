<?php

namespace App\Listeners;

use App\Events\TransactionStatusUpdate;
use App\Models\TransactionLog;
use Log;

class LogTransactionStatus
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TransactionStatusUpdate $event): void
    {
        // Log into file
        Log::info("Transaction ID: {$event->transaction->id} status changed from {$event->oldStatus} to {$event->newStatus}");

        // Log into DB
        TransactionLog::create([
            'transaction_id' => $event->transaction->id,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
            'message' => "Transaction status updated.",
        ]);
    }
}
