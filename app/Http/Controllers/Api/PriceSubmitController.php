<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Price;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PriceSubmitController extends Controller
{
    /**
     * Soumission d'un prix HDV par un joueur
     * POST /api/v1/prices
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id'   => 'required|exists:items,id',
            'server'    => 'required|in:draconiros,ombre,hellmina,orukam',
            'price_1'   => 'required|integer|min:1',
            'price_10'  => 'nullable|integer|min:1',
            'price_100' => 'nullable|integer|min:1',
        ]);

        // Déduire x10 et x100 si non fournis
        $price1   = $validated['price_1'];
        $price10  = $validated['price_10']  ?? (int)($price1 * 9.5);
        $price100 = $validated['price_100'] ?? (int)($price1 * 90);

        $price = Price::create([
            'item_id'     => $validated['item_id'],
            'server'      => $validated['server'],
            'price_1'     => $price1,
            'price_10'    => $price10,
            'price_100'   => $price100,
            'recorded_at' => Carbon::now(),
        ]);

        // Invalider le cache pour cet item/serveur
        Cache::forget("items.{$validated['server']}.."); // index
        Cache::forget("trending.{$validated['server']}");
        Cache::forget("craft.{$validated['server']}");

        $item = Item::find($validated['item_id']);

        return response()->json([
            'success' => true,
            'message' => "✅ Prix de {$item->name} enregistré sur {$validated['server']} !",
            'price'   => $price1,
            'item'    => $item->name,
            'server'  => $validated['server'],
        ]);
    }

    /**
     * Soumission en masse — plusieurs items d'un coup
     * POST /api/v1/prices/bulk
     */
    public function bulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server'          => 'required|in:draconiros,ombre,hellmina,orukam',
            'prices'          => 'required|array|min:1|max:50',
            'prices.*.item_id'=> 'required|exists:items,id',
            'prices.*.price_1'=> 'required|integer|min:1',
        ]);

        $server  = $validated['server'];
        $saved   = 0;
        $results = [];

        foreach ($validated['prices'] as $p) {
            $price1   = $p['price_1'];
            Price::create([
                'item_id'     => $p['item_id'],
                'server'      => $server,
                'price_1'     => $price1,
                'price_10'    => (int)($price1 * 9.5),
                'price_100'   => (int)($price1 * 90),
                'recorded_at' => Carbon::now(),
            ]);
            $results[] = $p['item_id'];
            $saved++;
        }

        Cache::flush();

        return response()->json([
            'success' => true,
            'message' => "✅ {$saved} prix enregistrés sur {$server} !",
            'saved'   => $saved,
        ]);
    }
}
