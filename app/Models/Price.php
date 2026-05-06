<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    protected $fillable = [
        'item_id',
        'server',
        'price_1',
        'price_10',
        'price_100',
        'volume',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'price_1' => 'integer',
        'price_10' => 'integer',
        'price_100' => 'integer',
        'volume' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    // Prix unitaire formaté en kamas
    public function getFormattedPrice1Attribute(): string
    {
        if (!$this->price_1) return 'N/A';
        return number_format($this->price_1, 0, ',', ' ') . ' K';
    }

    // Tendance : compare avec le prix précédent
    public function getTrendAttribute(): string
    {
        $previous = Price::where('item_id', $this->item_id)
            ->where('server', $this->server)
            ->where('recorded_at', '<', $this->recorded_at)
            ->latest('recorded_at')
            ->first();

        if (!$previous || !$previous->price_1 || !$this->price_1) return 'stable';

        $diff = (($this->price_1 - $previous->price_1) / $previous->price_1) * 100;

        if ($diff > 5) return 'up';
        if ($diff < -5) return 'down';
        return 'stable';
    }
}
