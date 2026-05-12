<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemPageController extends Controller
{
    public function show(string $slug, Request $request)
    {
        $item   = Item::where('slug', $slug)->firstOrFail();
        $server = $request->get('server', 'draconiros');

        // Enregistrer la vue
        $item->views()->create(['server' => $server]);

        $history = $item->prices()
            ->where('server', $server)
            ->orderBy('recorded_at')
            ->get()
            ->map(fn($p) => [
                'date'     => $p->recorded_at->format('Y-m-d'),
                'price_1'  => $p->price_1,
                'price_10' => $p->price_10,
                'price_100'=> $p->price_100,
            ]);

        $latest = $item->latestPrice($server);

        // Variation 24h
        $yesterday = $item->prices()
            ->where('server', $server)
            ->whereDate('recorded_at', now()->subDay()->toDateString())
            ->latest('recorded_at')
            ->first();

        $variation = null;
        if ($latest && $yesterday && $yesterday->price_1 > 0) {
            $variation = round((($latest->price_1 - $yesterday->price_1) / $yesterday->price_1) * 100, 1);
        }

        return view('items.show', compact('item', 'server', 'history', 'latest', 'variation'));
    }
}
