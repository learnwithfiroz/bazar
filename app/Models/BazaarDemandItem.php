<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BazaarDemandItem extends Model
{
    protected $fillable = [
        'bazaar_demand_id',
        'item_name',
        'category',
        'quantity',
        'unit',
        'estimated_price',
        'is_purchased',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'estimated_price' => 'decimal:2',
        'is_purchased' => 'boolean',
    ];

    public function demand(): BelongsTo
    {
        return $this->belongsTo(BazaarDemand::class, 'bazaar_demand_id');
    }
}
