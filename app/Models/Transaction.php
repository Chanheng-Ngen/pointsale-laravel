<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_number',
        'type',
        'description',
        'payment_method',
        'amount',
        'item_count',
        'order_id',
    ];

    protected $appends = ['time', 'date'];

    protected static function booted()
    {
        static::creating(function ($transaction) {
            if (empty($transaction->transaction_number)) {
                // Temporary number to be replaced after creation or use a unique generator
                $transaction->transaction_number = 'TXN-TEMP';
            }
        });

        static::created(function ($transaction) {
            if ($transaction->transaction_number === 'TXN-TEMP') {
                $transaction->update([
                    'transaction_number' => 'TXN-' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT),
                ]);
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getTimeAttribute()
    {
        return $this->created_at ? $this->created_at->format('h:i A') : '';
    }

    public function getDateAttribute()
    {
        return $this->created_at ? $this->created_at->format('Y-m-d') : '';
    }
}
