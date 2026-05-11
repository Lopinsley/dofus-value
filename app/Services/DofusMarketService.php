<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Price;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DofusMarketService
{
    const SERVERS = ['draconiros', 'ombre', 'hellmina', 'orukam'];

    const API_BASE = 'https://api.dofusdu.de/dofus3/v1/fr';

    /**
     * Scrape les prix de tous les items pour un serveur donné
     */
    public function scrapeServer(string $server): int
    {
        $items = Item::all();
        $count = 0;

        foreach ($items as $item) {
            try {
                $price = $this->fetchItemPrice($item->dofus_id, $server);
                if ($price) {
                    Price::create([
                        'item_id'     => $item->id,
                        'price_1'     => $price['price_1'],
                        'price_10'    => $price['price_10'],
                        'price_100'   => $price['price_100'],
                        'server'      => $server,
                        'recorded_at' => Carbon::now(),
                    ]);
                    $count++;
                }
                usleep(200000); // 200ms entre chaque requête (rate limit)
            } catch (\Exception $e) {
                Log::warning("Scrape failed for item {$item->dofus_id} on {$server}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Récupère le prix d'un item depuis l'API dofusdu.de
     */
    public function fetchItemPrice(int $dofusId, string $server): ?array
    {
        // Chercher dans toutes les catégories
        $categories = ['resources', 'consumables', 'equipment', 'quest-items'];

        foreach ($categories as $category) {
            $response = Http::timeout(10)
                ->get(self::API_BASE . "/items/{$category}/{$dofusId}", [
                    'server' => $server,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $prices = $data['prices'] ?? null;

                if ($prices) {
                    return [
                        'price_1'   => $prices['average_price'] ?? $prices['min_price'] ?? 0,
                        'price_10'  => isset($prices['average_price']) ? (int)($prices['average_price'] * 9.5) : 0,
                        'price_100' => isset($prices['average_price']) ? (int)($prices['average_price'] * 90) : 0,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Enrichit la base avec les vraies données de l'API (noms, images, recettes)
     */
    public function syncItemData(): int
    {
        $count = 0;
        $items = Item::all();

        foreach ($items as $item) {
            $categories = ['resources', 'consumables', 'equipment'];

            foreach ($categories as $category) {
                $response = Http::timeout(10)
                    ->get(self::API_BASE . "/items/{$category}/{$item->dofus_id}");

                if ($response->successful()) {
                    $data = $response->json();
                    $item->update([
                        'name'      => $data['name'] ?? $item->name,
                        'image_url' => $data['image_urls']['icon'] ?? $item->image_url,
                        'level'     => $data['level'] ?? $item->level,
                    ]);
                    $count++;
                    usleep(150000);
                    break;
                }
            }
        }

        return $count;
    }
}
