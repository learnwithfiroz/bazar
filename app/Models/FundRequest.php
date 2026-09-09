<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class FundRequest extends Model
{
    protected $fillable = [
        'wallet_id',
        'requested_by',
        'amount',
        'reason',
        'status',
        'action_by',
        'action_note',
        'action_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'action_at' => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne(WalletTransaction::class, 'reference');
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }
}
