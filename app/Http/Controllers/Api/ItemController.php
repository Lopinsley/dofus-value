<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Price;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ItemController extends Controller
{
    /**
     * Liste tous les items avec leur dernier prix
     */
    public function index(Request $request): JsonResponse
    {
        $server = $request->get('server', 'draconiros');
        $type   = $request->get('type');
        $search = $request->get('q');

        $items = Cache::remember("items.{$server}.{$type}.{$search}", 300, function () use ($server, $type, $search) {
            $query = Item::with(['prices' => function ($q) use ($server) {
                $q->where('server', $server)->latest('recorded_at')->limit(1);
            }]);

            if ($type) $query->where('type', $type);
            if ($search) $query->where('name', 'like', "%{$search}%");

            return $query->orderBy('name')->get()->map(fn($item) => $this->formatItem($item, $server));
        });

        return response()->json($items);
    }

    /**
     * Détail d'un item avec historique de prix
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $server = $request->get('server', 'draconiros');

        $item = Item::where('slug', $slug)->firstOrFail();

        $history = Price::where('item_id', $item->id)
            ->where('server', $server)
            ->orderBy('recorded_at')
            ->get()
            ->map(fn($p) => [
                'date'      => $p->recorded_at->toDateTimeString(),
                'price_x1'  => $p->price_x1,
                'price_x10' => $p->price_x10,
                'price_x100'=> $p->price_x100,
                'trend'     => $p->trend,
            ]);

        return response()->json([
            ...$this->formatItem($item, $server),
            'history' => $history,
            'craft'   => $item->craftMargin($server),
        ]);
    }

    /**
     * Items en tendance (plus forte variation dans les 24h)
     */
    public function trending(Request $request): JsonResponse
    {
        $server = $request->get('server', 'draconiros');

        $trending = Cache::remember("trending.{$server}", 600, function () use ($server) {
            return Item::with('prices')->get()
                ->map(function ($item) use ($server) {
                    $latest = $item->latestPrice($server);
                    if (!$latest) return null;
                    return [
                        ...$this->formatItem($item, $server),
                        'variation_percent' => $latest->variation_percent,
                    ];
                })
                ->filter()
                ->sortByDesc('variation_percent')
                ->values()
                ->take(20);
        });

        return response()->json($trending);
    }

    /**
     * Meilleures opportunités de craft
     */
    public function craftOpportunities(Request $request): JsonResponse
    {
        $server = $request->get('server', 'draconiros');
        $minMargin = $request->get('min_margin', 10); // % minimum

        $opportunities = Cache::remember("craft.{$server}.{$minMargin}", 600, function () use ($server, $minMargin) {
            return Item::whereNotNull('recipe')->get()
                ->map(function ($item) use ($server, $minMargin) {
                    $margin = $item->craftMargin($server);
                    if (!$margin || $margin['margin_percent'] < $minMargin) return null;
                    return [
                        ...$this->formatItem($item, $server),
                        'craft' => $margin,
                    ];
                })
                ->filter()
                ->sortByDesc('craft.margin_percent')
                ->values();
        });

        return response()->json($opportunities);
    }

    private function formatItem(Item $item, string $server): array
    {
        $latest = $item->latestPrice($server);
        return [
            'id'        => $item->id,
            'dofus_id'  => $item->dofus_id,
            'name'      => $item->name,
            'slug'      => $item->slug,
            'type'      => $item->type,
            'level'     => $item->level,
            'image_url' => $item->image_url,
            'price'     => $latest?->price_x1,
            'price_x10' => $latest?->price_x10,
            'price_x100'=> $latest?->price_x100,
            'trend'     => $latest?->trend ?? 'stable',
            'variation' => $latest?->variation_percent ?? 0,
        ];
    }
}
