<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DofusApiService
{
    private string $baseUrl;
    private string $gameVersion;
    private string $language;

    public function __construct()
    {
        $this->baseUrl = config('dofus.api.base_url');
        $this->gameVersion = config('dofus.api.game_version');
        $this->language = config('dofus.api.language');
    }

    private function get(string $endpoint, array $params = [], int $cacheTtl = 3600): ?array
    {
        $url = "{$this->baseUrl}/{$this->gameVersion}/{$this->language}/{$endpoint}";
        $cacheKey = "dofus_api:" . md5($url . serialize($params));

        return Cache::remember($cacheKey, $cacheTtl, function () use ($url, $params) {
            try {
                $response = Http::timeout(config('dofus.api.timeout', 30))
                    ->retry(config('dofus.api.retry', 3), 1000)
                    ->get($url, $params);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning('DofusAPI: Request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('DofusAPI: Exception', ['message' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Récupère la liste des items avec pagination
     */
    public function getItems(int $pageSize = 50, int $pageNumber = 1): ?array
    {
        return $this->get('items/equipment', [
            'page[size]' => $pageSize,
            'page[number]' => $pageNumber,
        ]);
    }

    /**
     * Récupère les ressources
     */
    public function getResources(int $pageSize = 50, int $pageNumber = 1): ?array
    {
        return $this->get('items/resources', [
            'page[size]' => $pageSize,
            'page[number]' => $pageNumber,
        ]);
    }

    /**
     * Récupère les consommables
     */
    public function getConsumables(int $pageSize = 50, int $pageNumber = 1): ?array
    {
        return $this->get('items/consumables', [
            'page[size]' => $pageSize,
            'page[number]' => $pageNumber,
        ]);
    }

    /**
     * Récupère un item spécifique par son ID Dofus
     */
    public function getItem(int $dofusId, string $type = 'equipment'): ?array
    {
        return $this->get("items/{$type}/{$dofusId}");
    }

    /**
     * Recherche d'items par nom
     */
    public function searchItems(string $query, string $type = 'equipment'): ?array
    {
        return $this->get("items/{$type}/search", [
            'query' => $query,
            'limit' => 20,
        ]);
    }

    /**
     * Récupère les recettes de craft
     */
    public function getRecipes(int $pageSize = 50, int $pageNumber = 1): ?array
    {
        return $this->get('recipes', [
            'page[size]' => $pageSize,
            'page[number]' => $pageNumber,
        ]);
    }
}
