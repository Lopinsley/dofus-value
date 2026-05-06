<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Price;
use Illuminate\Support\Collection;

class PriceAnalyzer
{
    /**
     * Calcule les statistiques de prix pour un item
     */
    public function getStats(Item $item, int $days = 30, string $server = 'all'): array
    {
        $history = $item->priceHistory($days, $server);

        if ($history->isEmpty()) {
            return $this->emptyStats();
        }

        $prices = $history->pluck('price_1')->filter();

        if ($prices->isEmpty()) {
            return $this->emptyStats();
        }

        $latest = $history->last();
        $oldest = $history->first();

        return [
            'current' => $latest->price_1,
            'min' => $prices->min(),
            'max' => $prices->max(),
            'avg' => round($prices->average()),
            'trend' => $this->calculateTrend($oldest->price_1, $latest->price_1),
            'trend_percent' => $this->calculateTrendPercent($oldest->price_1, $latest->price_1),
            'volatility' => $this->calculateVolatility($prices),
            'chart_data' => $this->formatChartData($history),
        ];
    }

    /**
     * Calcule les meilleures opportunités de craft
     */
    public function getBestCraftOpportunities(int $limit = 20): Collection
    {
        return Item::whereNotNull('recipe')
            ->get()
            ->map(function ($item) {
                return [
                    'item' => $item,
                    'sell_price' => $item->latestPrice()?->price_1,
                    'craft_cost' => $item->craftCost(),
                    'margin' => $item->craftMargin(),
                ];
            })
            ->filter(fn($data) => $data['margin'] !== null && $data['margin'] > 0)
            ->sortByDesc('margin')
            ->take($limit)
            ->values();
    }

    /**
     * Retourne les items les plus tendance (hausse ou baisse forte)
     */
    public function getTrendingItems(int $limit = 10): array
    {
        $rising = [];
        $falling = [];

        Item::with(['prices' => function ($q) {
            $q->where('recorded_at', '>=', now()->subDays(7))
              ->orderBy('recorded_at');
        }])->chunk(100, function ($items) use (&$rising, &$falling) {
            foreach ($items as $item) {
                if ($item->prices->count() < 2) continue;

                $first = $item->prices->first()->price_1;
                $last = $item->prices->last()->price_1;

                if (!$first || !$last) continue;

                $percent = $this->calculateTrendPercent($first, $last);

                if ($percent > 10) {
                    $rising[] = ['item' => $item, 'change_percent' => $percent];
                } elseif ($percent < -10) {
                    $falling[] = ['item' => $item, 'change_percent' => $percent];
                }
            }
        });

        usort($rising, fn($a, $b) => $b['change_percent'] <=> $a['change_percent']);
        usort($falling, fn($a, $b) => $a['change_percent'] <=> $b['change_percent']);

        return [
            'rising' => array_slice($rising, 0, $limit),
            'falling' => array_slice($falling, 0, $limit),
        ];
    }

    private function calculateTrend(?int $from, ?int $to): string
    {
        if (!$from || !$to) return 'stable';
        $percent = $this->calculateTrendPercent($from, $to);
        if ($percent > 5) return 'up';
        if ($percent < -5) return 'down';
        return 'stable';
    }

    private function calculateTrendPercent(?int $from, ?int $to): float
    {
        if (!$from || !$to || $from === 0) return 0;
        return round((($to - $from) / $from) * 100, 2);
    }

    private function calculateVolatility(Collection $prices): string
    {
        if ($prices->count() < 3) return 'low';
        $avg = $prices->average();
        if ($avg == 0) return 'low';
        $variance = $prices->map(fn($p) => pow($p - $avg, 2))->average();
        $stdDev = sqrt($variance);
        $cv = ($stdDev / $avg) * 100;
        if ($cv > 30) return 'high';
        if ($cv > 10) return 'medium';
        return 'low';
    }

    private function formatChartData(Collection $history): array
    {
        return [
            'labels' => $history->map(fn($p) => $p->recorded_at->format('d/m H:i'))->toArray(),
            'prices' => $history->map(fn($p) => $p->price_1)->toArray(),
            'volumes' => $history->map(fn($p) => $p->volume)->toArray(),
        ];
    }

    private function emptyStats(): array
    {
        return [
            'current' => null,
            'min' => null,
            'max' => null,
            'avg' => null,
            'trend' => 'stable',
            'trend_percent' => 0,
            'volatility' => 'low',
            'chart_data' => ['labels' => [], 'prices' => [], 'volumes' => []],
        ];
    }
}
