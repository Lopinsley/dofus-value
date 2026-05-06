<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\PriceAnalyzer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct(private readonly PriceAnalyzer $analyzer) {}

    /**
     * GET /api/items — Liste paginée avec recherche
     */
    public function index(Request $request): JsonResponse
    {
        $query = Item::query();

        if ($search = $request->get('q')) {
            $query->search($search);
        }

        if ($category = $request->get('category')) {
            $query->byCategory($category);
        }

        if ($level = $request->get('level')) {
            $query->where('level', '<=', $level);
        }

        $items = $query->orderBy('name')
            ->paginate($request->get('per_page', 24));

        return response()->json([
            'data' => $items->items(),
            'meta' => [
                'total' => $items->total(),
                'per_page' => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/items/{slug} — Détail d'un item avec stats de prix
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $item = Item::where('slug', $slug)->firstOrFail();
        $days = (int) $request->get('days', 30);
        $server = $request->get('server', 'all');

        $stats = $this->analyzer->getStats($item, $days, $server);

        return response()->json([
            'item' => $item,
            'stats' => $stats,
            'craft' => [
                'cost' => $item->craftCost(),
                'margin' => $item->craftMargin(),
                'recipe' => $item->recipe,
            ],
        ]);
    }

    /**
     * GET /api/items/trending — Items les plus tendance
     */
    public function trending(): JsonResponse
    {
        $trending = $this->analyzer->getTrendingItems(10);

        return response()->json($trending);
    }

    /**
     * GET /api/items/craft-opportunities — Meilleures opportunités de craft
     */
    public function craftOpportunities(Request $request): JsonResponse
    {
        $opportunities = $this->analyzer->getBestCraftOpportunities(
            (int) $request->get('limit', 20)
        );

        return response()->json(['data' => $opportunities]);
    }
}
