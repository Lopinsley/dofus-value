<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Price;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $server = $request->get('server', 'draconiros');
        $type   = $request->get('type');
        $search = $request->get('q');

        $cacheKey = "items.{$server}.{$type}.{$search}";

        // TTL court (30s) en dev, 5min en prod
        $ttl = app()->isProduction() ? 300 : 30;

        $items = Cache::remember($cacheKey, $ttl, function () use ($server, $type, $search) {
            $query = Item::query();
            if ($type)   $query->where('category', $type);
            if ($search) $query->where('name', 'like', "%{$search}%");

            return $query->orderBy('name')->get()->map(fn($item) => $this->formatItem($item, $server));
        });

        return response()->json($items);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $server = $request->get('server', 'draconiros');
        $item   = Item::where('slug', $slug)->firstOrFail();

        $history = Price::where('item_id', $item->id)
            ->where('server', $server)
            ->orderBy('recorded_at')
            ->get()
            ->map(fn($p) => [
                'date'       => $p->recorded_at->toDateTimeString(),
                'price_1'    => $p->price_1,
                'price_10'   => $p->price_10,
                'price_100'  => $p->price_100,
            ]);

        return response()->json([
            ...$this->formatItem($item, $server),
            'history' => $history,
        ]);
    }

    public function trending(Request $request): JsonResponse
    {
        $server = $request->get('server', 'draconiros');

        $trending = Cache::remember("trending.{$server}", app()->isProduction() ? 300 : 30, function () use ($server) {
            return Item::all()->map(function ($item) use ($server) {
                $latest = $this->getLatestPrice($item->id, $server);
                $prev   = $this->getPreviousPrice($item->id, $server, $latest?->recorded_at);

                if (!$latest) return null;

                $variation = 0;
                if ($prev && $prev->price_1 > 0) {
                    $variation = round((($latest->price_1 - $prev->price_1) / $prev->price_1) * 100, 2);
                }

                return array_merge($this->formatItem($item, $server), ['variation_percent' => $variation]);
            })
            ->filter()
            ->sortByDesc('variation_percent')
            ->values()
            ->take(20);
        });

        return response()->json($trending);
    }

    public function craftOpportunities(Request $request): JsonResponse
    {
        return response()->json([]); // À implémenter quand recettes disponibles
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function getLatestPrice(int $itemId, string $server): ?Price
    {
        return Price::where('item_id', $itemId)
            ->where('server', $server)
            ->orderByDesc('recorded_at')
            ->first();
    }

    private function getPreviousPrice(int $itemId, string $server, $before): ?Price
    {
        if (!$before) return null;
        return Price::where('item_id', $itemId)
            ->where('server', $server)
            ->where('recorded_at', '<', $before)
            ->orderByDesc('recorded_at')
            ->first();
    }

    private function formatItem(Item $item, string $server): array
    {
        $latest    = $this->getLatestPrice($item->id, $server);
        $prev      = $this->getPreviousPrice($item->id, $server, $latest?->recorded_at);
        $variation = 0;
        $trend     = 'stable';

        if ($latest && $prev && $prev->price_1 > 0) {
            $variation = round((($latest->price_1 - $prev->price_1) / $prev->price_1) * 100, 2);
            if ($variation > 5)  $trend = 'up';
            if ($variation < -5) $trend = 'down';
        }

        return [
            'id'         => $item->id,
            'dofus_id'   => $item->dofus_id,
            'name'       => $item->name,
            'slug'       => $item->slug,
            'type'       => $item->category,
            'level'      => $item->level,
            'image_url'  => $item->image_url,
            'price'      => $latest?->price_1,
            'price_x10'  => $latest?->price_10,
            'price_x100' => $latest?->price_100,
            'trend'      => $trend,
            'variation'  => $variation,
        ];
    }
}
