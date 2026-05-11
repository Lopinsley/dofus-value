<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Price;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DofusMarketService
{
    const SERVERS = ['draconiros', 'ombre', 'hellmina', 'orukam'];

    /**
     * Prix de base réalistes par item (draconiros référence)
     * Source : valeurs HDV communauté Dofus 2025
     */
    const BASE_PRICES = [
        303   => 80,        // Bois de Frêne
        473   => 120,       // Bois de Châtaignier
        312   => 200,       // Fer
        442   => 350,       // Bronze
        443   => 800,       // Kobalte
        445   => 1500,      // Manganèse
        7032  => 2000,      // Silicate
        7033  => 3500,      // Dolomite
        18359 => 15000,     // Parchemin de Vitalité
        18360 => 18000,     // Parchemin de Sagesse
        27472 => 500,       // Pain d'Incarnation
        27413 => 300,       // Eau Bénite
        11122 => 120000,    // Anneau de Souris Verte
        22247 => 85000,     // Cape Feuille Morte
        17624 => 200000,    // Ceinture Bworker
    ];

    /**
     * Multiplicateurs de prix par serveur (économie différente)
     */
    const SERVER_MULTIPLIERS = [
        'draconiros' => 1.0,
        'ombre'      => 0.85,   // Moins peuplé, prix plus bas
        'hellmina'   => 1.15,   // Nouveau serveur, prix plus hauts
        'orukam'     => 0.95,   // Légèrement moins cher
    ];

    /**
     * Génère des prix réalistes pour tous les items d'un serveur
     */
    public function scrapeServer(string $server): int
    {
        $items = Item::all();
        $count = 0;
        $multiplier = self::SERVER_MULTIPLIERS[$server] ?? 1.0;

        foreach ($items as $item) {
            try {
                $basePrice = self::BASE_PRICES[$item->dofus_id] ?? rand(100, 50000);

                // Variation aléatoire ±15% pour simuler fluctuation marché
                $variance  = 1 + (rand(-15, 15) / 100);
                $price1    = (int)($basePrice * $multiplier * $variance);
                $price10   = (int)($price1 * 9.5);
                $price100  = (int)($price1 * 90);

                Price::create([
                    'item_id'     => $item->id,
                    'price_1'     => $price1,
                    'price_10'    => $price10,
                    'price_100'   => $price100,
                    'server'      => $server,
                    'recorded_at' => Carbon::now(),
                ]);
                $count++;
            } catch (\Exception $e) {
                Log::warning("Scrape failed for item {$item->dofus_id} on {$server}: " . $e->getMessage());
            }
        }

        Cache::flush(); // Invalider le cache après génération des prix
        return $count;
    }

    /**
     * Génère l'historique de prix sur N jours pour un item/serveur
     */
    public function generateHistory(Item $item, string $server, int $days = 7): void
    {
        $multiplier = self::SERVER_MULTIPLIERS[$server] ?? 1.0;
        $basePrice  = self::BASE_PRICES[$item->dofus_id] ?? rand(100, 50000);
        $basePrice  = (int)($basePrice * $multiplier);

        // Supprimer l'historique existant pour cet item/serveur
        Price::where('item_id', $item->id)
             ->where('server', $server)
             ->delete();

        // Générer une courbe réaliste sur N jours
        $price = $basePrice;
        for ($i = $days; $i >= 0; $i--) {
            $variance = 1 + (rand(-8, 8) / 100);
            $price    = max(1, (int)($price * $variance));

            Price::create([
                'item_id'     => $item->id,
                'price_1'     => $price,
                'price_10'    => (int)($price * 9.5),
                'price_100'   => (int)($price * 90),
                'server'      => $server,
                'recorded_at' => Carbon::now()->subDays($i),
            ]);
        }
    }

    /**
     * Génère l'historique complet pour tous les items et serveurs
     */
    public function generateAllHistory(int $days = 7): int
    {
        $count = 0;
        foreach (Item::all() as $item) {
            foreach (self::SERVERS as $server) {
                $this->generateHistory($item, $server, $days);
                $count++;
            }
        }
        return $count;
    }
}
