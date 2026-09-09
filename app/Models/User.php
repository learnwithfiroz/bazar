<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'role',
        'is_active',
        'avatar',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'created_by');
    }

    public function fundRequests(): HasMany
    {
        return $this->hasMany(FundRequest::class, 'requested_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ExpenseComment::class, 'user_id');
    }

    // Role Helper Methods
    public function isPrincipal(): bool
    {
        return $this->role === 'principal';
    }

    public function isPA(): bool
    {
        return $this->role === 'pa';
    }

    public function isMessenger(): bool
    {
        return $this->role === 'messenger';
    }

    public function isFamily(): bool
    {
        return $this->role === 'family';
    }

    public function canManageFunds(): bool
    {
        return in_array($this->role, ['principal', 'pa']);
    }

    public function getRoleDisplayNameAttribute(): string
    {
        return match ($this->role) {
            'principal' => 'প্রিন্সিপাল (Super Admin)',
            'pa' => 'প্রিন্সিপালের পিএ (Manager)',
            'messenger' => 'মেসেঞ্জার (Staff)',
            'family' => 'পরিবারের সদস্য (Viewer)',
            default => ucfirst($this->role),
        };
    }
}
