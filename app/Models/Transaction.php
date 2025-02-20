<?php

namespace App\Models;

use App\Events\TransactionStatusUpdate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($transaction) {
            if ($transaction->isDirty('status')) {
                event(new TransactionStatusUpdate(
                    $transaction,
                    $transaction->getOriginal('status'),
                    $transaction->status
                ));
            }
        });
    }

    protected $fillable = ['user_id', 'transfer_id', 'amount', 'status'];

    private $statusOption = [
        0 => 'Pending',
        10 => 'Completed',
        20 => 'Failed',
        30 => 'Canceled',
    ];

    /**
     * Define the relationship with User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transfer()
    {
        return $this->belongsTo(Transfer::class);
    }

    /**
     *  Get status option to be returned.
     */
    public function getStatusOption()
    {
        return $this->statusOption;
    }

    /**
     * Get the human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->statusOption[$this->status] ?? 'Unknown';
    }
}
