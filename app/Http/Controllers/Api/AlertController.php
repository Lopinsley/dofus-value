<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\PriceAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $alerts = PriceAlert::with('item')
            ->where('triggered', false)
            ->get()
            ->map(fn($a) => [
                'id'              => $a->id,
                'item'            => ['name' => $a->item->name, 'slug' => $a->item->slug, 'image_url' => $a->item->image_url],
                'server'          => $a->server,
                'threshold_price' => $a->threshold_price,
                'direction'       => $a->direction,
                'email'           => $a->email,
            ]);

        return response()->json($alerts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id'         => 'required|exists:items,id',
            'server'          => 'required|in:draconiros,ombre,hellmina,orukam',
            'threshold_price' => 'required|integer|min:1',
            'direction'       => 'required|in:below,above',
            'email'           => 'nullable|email',
        ]);

        $alert = PriceAlert::create($validated);

        return response()->json($alert, 201);
    }

    public function destroy(int $id): JsonResponse
    {
        PriceAlert::findOrFail($id)->delete();
        return response()->json(['message' => 'Alerte supprimée']);
    }
}
