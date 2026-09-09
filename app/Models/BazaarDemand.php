<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BazaarDemand extends Model
{
    protected $fillable = [
        'created_by',
        'assigned_to',
        'title',
        'target_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'target_date' => 'date:Y-m-d',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BazaarDemandItem::class);
    }

    public function getPurchasedCountAttribute(): int
    {
        return $this->items()->where('is_purchased', true)->count();
    }

    public function getTotalItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    public function getProgressPercentAttribute(): int
    {
        $total = $this->total_items_count;
        if ($total === 0) return 0;
        return (int) round(($this->purchased_count / $total) * 100);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'PENDING' => 'bg-amber-100 text-amber-800 border-amber-300',
            'IN_PROGRESS' => 'bg-blue-100 text-blue-800 border-blue-300',
            'COMPLETED' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'PENDING' => 'অপেক্ষমান (Pending)',
            'IN_PROGRESS' => 'কেনাকাটা চলছে (In Progress)',
            'COMPLETED' => 'সম্পন্ন (Completed)',
            default => $this->status,
        };
    }
}
