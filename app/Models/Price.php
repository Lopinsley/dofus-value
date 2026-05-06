<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    protected $fillable = [
        'item_id', 'price_1', 'price_10', 'price_100',
        'server', 'recorded_at'
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Calcule la tendance par rapport au prix précédent
     */
    public function getTrendAttribute(): string
    {
        $previous = Price::where('item_id', $this->item_id)
            ->where('server', $this->server)
            ->where('recorded_at', '<', $this->recorded_at)
            ->latest('recorded_at')
            ->first();

        if (!$previous) return 'stable';

        $diff = $this->price_1 - $previous->price_1;
        $percent = $previous->price_1 > 0 ? ($diff / $previous->price_1) * 100 : 0;

        if ($percent > 5) return 'up';
        if ($percent < -5) return 'down';
        return 'stable';
    }

    /**
     * Retourne le pourcentage de variation
     */
    public function getVariationPercentAttribute(): float
    {
        $previous = Price::where('item_id', $this->item_id)
            ->where('server', $this->server)
            ->where('recorded_at', '<', $this->recorded_at)
            ->latest('recorded_at')
            ->first();

        if (!$previous || $previous->price_1 === 0) return 0.0;

        return round((($this->price_1 - $previous->price_1) / $previous->price_1) * 100, 2);
    }
}
