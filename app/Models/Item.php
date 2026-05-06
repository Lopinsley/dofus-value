<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Item extends Model
{
    protected $fillable = [
        'dofus_id',
        'name',
        'slug',
        'category',
        'level',
        'image_url',
        'recipe',
        'effects',
    ];

    protected $casts = [
        'recipe' => 'array',
        'effects' => 'array',
    ];

    // Relations
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(PriceAlert::class);
    }

    // Scopes
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where('name', 'like', "%{$term}%");
    }

    // Helpers
    public function latestPrice(string $server = 'all'): ?Price
    {
        return $this->prices()
            ->where('server', $server)
            ->latest('recorded_at')
            ->first();
    }

    public function priceHistory(int $days = 30, string $server = 'all')
    {
        return $this->prices()
            ->where('server', $server)
            ->where('recorded_at', '>=', now()->subDays($days))
            ->orderBy('recorded_at')
            ->get();
    }

    public function craftCost(): ?int
    {
        if (!$this->recipe) return null;

        $total = 0;
        foreach ($this->recipe as $ingredient) {
            $item = self::where('dofus_id', $ingredient['item_id'])->first();
            if (!$item) continue;
            $price = $item->latestPrice()?->price_1;
            if (!$price) return null;
            $total += $price * $ingredient['quantity'];
        }

        return $total;
    }

    public function craftMargin(): ?float
    {
        $craftCost = $this->craftCost();
        $sellPrice = $this->latestPrice()?->price_1;

        if (!$craftCost || !$sellPrice) return null;

        return round((($sellPrice - $craftCost) / $craftCost) * 100, 2);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->slug = Str::slug($item->name);
        });
    }
}
