<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'current_balance',
        'low_balance_alert_limit',
        'currency',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'low_balance_alert_limit' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    public function fundRequests(): HasMany
    {
        return $this->hasMany(FundRequest::class)->latest();
    }

    public function isLowBalance(): bool
    {
        return $this->current_balance <= $this->low_balance_alert_limit;
    }
}
