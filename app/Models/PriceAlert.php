<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceAlert extends Model
{
    protected $fillable = [
        'item_id', 'server', 'threshold_price', 'direction', 'email', 'triggered', 'triggered_at'
    ];

    protected $casts = [
        'triggered'    => 'boolean',
        'triggered_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
