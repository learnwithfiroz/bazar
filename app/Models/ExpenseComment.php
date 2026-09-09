<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseComment extends Model
{
    protected $fillable = [
        'expense_id',
        'user_id',
        'parent_id',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ExpenseComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ExpenseComment::class, 'parent_id')->with('user')->oldest();
    }
}
