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
            // Ressources — IDs & images vérifiés via api.dofusdu.de/dofus3
            ['dofus_id' => 303,   'name' => 'Bois de Frêne',        'category' => 'ressource',    'level' => 20,  'slug' => 'bois-de-frene',       'img_id' => 38017],
            ['dofus_id' => 473,   'name' => 'Bois de Châtaignier',  'category' => 'ressource',    'level' => 40,  'slug' => 'bois-de-chataignier', 'img_id' => 38086],
            ['dofus_id' => 312,   'name' => 'Fer',                  'category' => 'ressource',    'level' => 10,  'slug' => 'fer',                  'img_id' => 39024],
            ['dofus_id' => 442,   'name' => 'Bronze',               'category' => 'ressource',    'level' => 30,  'slug' => 'bronze',               'img_id' => 39109],
            ['dofus_id' => 443,   'name' => 'Kobalte',              'category' => 'ressource',    'level' => 50,  'slug' => 'kobalte',              'img_id' => 39077],
            ['dofus_id' => 445,   'name' => 'Manganèse',            'category' => 'ressource',    'level' => 70,  'slug' => 'manganese',            'img_id' => 39397],
            ['dofus_id' => 7032,  'name' => 'Silicate',             'category' => 'ressource',    'level' => 80,  'slug' => 'silicate',             'img_id' => 39111],
            ['dofus_id' => 7033,  'name' => 'Dolomite',             'category' => 'ressource',    'level' => 100, 'slug' => 'dolomite',             'img_id' => 39110],
            // Parchemins
            ['dofus_id' => 18359, 'name' => 'Parchemin de Vitalité','category' => 'parchemin',    'level' => 1,   'slug' => 'parchemin-vitalite',  'img_id' => 15951],
            ['dofus_id' => 18360, 'name' => 'Parchemin de Sagesse', 'category' => 'parchemin',    'level' => 1,   'slug' => 'parchemin-sagesse',   'img_id' => 15951],
            // Consommables
            ['dofus_id' => 27472, 'name' => "Pain d'Incarnation",   'category' => 'consommable',  'level' => 1,   'slug' => 'pain-incarnation',    'img_id' => 33407],
            ['dofus_id' => 27413, 'name' => 'Eau Bénite',           'category' => 'consommable',  'level' => 1,   'slug' => 'eau-benite',           'img_id' => 39404],
            // Équipements
            ['dofus_id' => 11122, 'name' => 'Anneau de Souris Verte','category' => 'anneau',      'level' => 15,  'slug' => 'anneau-souris-verte', 'img_id' => 47661],
            ['dofus_id' => 22247, 'name' => 'Cape Feuille Morte',   'category' => 'cape',         'level' => 30,  'slug' => 'cape-feuille-morte',  'img_id' => 55740],
            ['dofus_id' => 17624, 'name' => 'Ceinture Bworker',     'category' => 'ceinture',     'level' => 60,  'slug' => 'ceinture-bworker',    'img_id' => 15032],
        ];

        foreach ($items as $itemData) {
            $imgId = $itemData['img_id'];
            unset($itemData['img_id']);

            $item = Item::updateOrCreate(
                ['dofus_id' => $itemData['dofus_id']],
                array_merge($itemData, [
                    'image_url' => "https://api.dofusdu.de/dofus3/v1/img/item/{$imgId}-64.png"
                ])
            );

            // Historique de prix sur 7 jours
            $basePrice = rand(1000, 500000);
            for ($i = 7; $i >= 0; $i--) {
                $variation = rand(-10, 10) / 100;
                $price = (int) ($basePrice * (1 + $variation));
                $basePrice = $price;

                Price::create([
                    'item_id'     => $item->id,
                    'price_1'     => $price,
                    'price_10'    => (int) ($price * 9.5),
                    'price_100'   => (int) ($price * 90),
                    'server'      => 'draconiros',
                    'recorded_at' => Carbon::now()->subDays($i),
                ]);
            }
        }

        $this->command->info('✅ ' . count($items) . ' items Dofus seedés avec images vérifiées !');

        // Vider le cache pour que les nouvelles données soient visibles immédiatement
        \Illuminate\Support\Facades\Cache::flush();
        $this->command->info('🧹 Cache vidé.');
    }
}
