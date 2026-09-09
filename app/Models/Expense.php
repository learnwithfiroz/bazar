<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Expense extends Model
{
    protected $fillable = [
        'created_by',
        'expense_date',
        'title',
        'memo_no',
        'vendor_name',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date:Y-m-d',
        'total_amount' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpenseItem::class);
    }

    public function slips(): HasMany
    {
        return $this->hasMany(Slip::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ExpenseComment::class)->whereNull('parent_id')->with('replies.user', 'user')->latest();
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(ExpenseComment::class)->with('user');
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne(WalletTransaction::class, 'reference');
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->expense_date ? $this->expense_date->format('Y-m-d') : '';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'SUBMITTED' => 'bg-amber-100 text-amber-800 border-amber-300',
            'REVIEWED' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'FLAGGED' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'SUBMITTED' => 'পর্যালোচনার অপেক্ষায় (Submitted)',
            'REVIEWED' => 'অনুমোদিত / যাচাইকৃত (Reviewed)',
            'FLAGGED' => 'প্রশ্নবিদ্ধ / সংশোধনীয় (Flagged)',
            default => $this->status,
        };
    }
}
