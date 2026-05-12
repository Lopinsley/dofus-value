<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'dofus_id', 'name', 'slug', 'category', 'level',
        'image_url', 'effects', 'recipe', 'craft_cost'
    ];

    protected $casts = [
        'effects' => 'array',
        'recipe' => 'array',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(ItemView::class);
    }

    public function latestPrice(string $server = 'draconiros'): ?Price
    {
        return $this->prices()
            ->where('server', $server)
            ->latest('recorded_at')
            ->first();
    }

    /**
     * Calcule le coût de craft basé sur les prix actuels des ingrédients
     */
    public function craftCost(string $server = 'draconiros'): ?int
    {
        if (empty($this->recipe)) return null;

        $total = 0;
        foreach ($this->recipe as $ingredient) {
            $ingredientItem = Item::where('dofus_id', $ingredient['id'])->first();
            if (!$ingredientItem) return null;

            $price = $ingredientItem->latestPrice($server);
            if (!$price) return null;

            $total += $price->price_1 * $ingredient['quantity'];
        }

        return $total;
    }

    /**
     * Calcule la marge de craft (prix HDV - coût craft)
     */
    public function craftMargin(string $server = 'draconiros'): ?array
    {
        $cost = $this->craftCost($server);
        if (!$cost) return null;

        $latestPrice = $this->latestPrice($server);
        if (!$latestPrice) return null;

        $sellPrice = $latestPrice->price_1;
        $margin = $sellPrice - $cost;
        $marginPercent = $cost > 0 ? round(($margin / $cost) * 100, 1) : 0;

        return [
            'craft_cost' => $cost,
            'sell_price' => $sellPrice,
            'margin' => $margin,
            'margin_percent' => $marginPercent,
            'is_profitable' => $margin > 0,
        ];
    }
}
