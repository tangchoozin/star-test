<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionStatusUpdate
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $transaction;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Transaction $transaction, $oldStatus, $newStatus)
    {
        $this->transaction = $transaction;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}
