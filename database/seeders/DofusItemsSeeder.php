<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Price;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DofusItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Ressources populaires
            ['dofus_id' => 472,  'name' => 'Bois de Frêne',      /* type */ 'category' => 'ressource',    'level' => 20,  'slug' => 'bois-de-frene'],
            ['dofus_id' => 473,  'name' => 'Bois de Châtaignier',/* type */ 'category' => 'ressource',    'level' => 40,  'slug' => 'bois-de-chataignier'],
            ['dofus_id' => 440,  'name' => 'Fer',                /* type */ 'category' => 'ressource',    'level' => 10,  'slug' => 'fer'],
            ['dofus_id' => 441,  'name' => 'Bronze',             /* type */ 'category' => 'ressource',    'level' => 30,  'slug' => 'bronze'],
            ['dofus_id' => 442,  'name' => 'Kobalte',            /* type */ 'category' => 'ressource',    'level' => 50,  'slug' => 'kobalte'],
            ['dofus_id' => 443,  'name' => 'Manganèse',          /* type */ 'category' => 'ressource',    'level' => 70,  'slug' => 'manganese'],
            ['dofus_id' => 444,  'name' => 'Silicate',           /* type */ 'category' => 'ressource',    'level' => 80,  'slug' => 'silicate'],
            ['dofus_id' => 445,  'name' => 'Dolomite',           /* type */ 'category' => 'ressource',    'level' => 100, 'slug' => 'dolomite'],
            // Parchemins XP
            ['dofus_id' => 531,  'name' => 'Parchemin de Vitalité', /* type */ 'category' => 'parchemin', 'level' => 1,   'slug' => 'parchemin-vitalite'],
            ['dofus_id' => 532,  'name' => 'Parchemin de Sagesse',  /* type */ 'category' => 'parchemin', 'level' => 1,   'slug' => 'parchemin-sagesse'],
            // Consommables craft
            ['dofus_id' => 2661, 'name' => 'Pain d\'Incarnation',   /* type */ 'category' => 'consommable','level' => 1,  'slug' => 'pain-incarnation'],
            ['dofus_id' => 2662, 'name' => 'Eau Bénite',            /* type */ 'category' => 'consommable','level' => 1,  'slug' => 'eau-benite'],
            // Équipements populaires
            ['dofus_id' => 7771, 'name' => 'Anneau de Souris Verte',/* type */ 'category' => 'anneau',    'level' => 15,  'slug' => 'anneau-souris-verte'],
            ['dofus_id' => 7920, 'name' => 'Cape Feuille Morte',    /* type */ 'category' => 'cape',      'level' => 30,  'slug' => 'cape-feuille-morte'],
            ['dofus_id' => 9753, 'name' => 'Ceinture Bworker',      /* type */ 'category' => 'ceinture',  'level' => 60,  'slug' => 'ceinture-bworker'],
        ];

        foreach ($items as $itemData) {
            $item = Item::updateOrCreate(
                ['dofus_id' => $itemData['dofus_id']],
                array_merge($itemData, [
                    'image_url' => "https://api.dofusdu.de/dofus3/v1/img/item/{$itemData['dofus_id']}-64.png"
                ])
            );

            // Générer un historique de prix sur 7 jours
            $basePrice = rand(100, 500000);
            for ($i = 7; $i >= 0; $i--) {
                $variation = rand(-10, 10) / 100; // ±10%
                $price = (int) ($basePrice * (1 + $variation));
                $basePrice = $price;

                Price::create([
                    'item_id'     => $item->id,
                    'price_1'    => $price,
                    'price_10'   => (int) ($price * 9.5),
                    'price_100'  => (int) ($price * 90),
                    'server'      => 'draconiros',
                    'recorded_at' => Carbon::now()->subDays($i),
                ]);
            }
        }

        $this->command->info('✅ ' . count($items) . ' items Dofus seedés avec historique de prix !');
    }
}
