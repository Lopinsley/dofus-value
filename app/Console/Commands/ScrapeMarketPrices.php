<?php

namespace App\Console\Commands;

use App\Services\DofusMarketService;
use Illuminate\Console\Command;

class ScrapeMarketPrices extends Command
{
    protected $signature   = 'dofus:scrape {--server=all : Server (draconiros/ombre/hellmina/orukam/all)} {--history : Générer historique 7j}';
    protected $description = 'Génère des prix de marché réalistes pour DofusValue';

    public function handle(DofusMarketService $service): void
    {
        if ($this->option('history')) {
            $this->info('📈 Génération de l\'historique 7 jours pour tous les serveurs...');
            $count = $service->generateAllHistory(7);
            $this->info("✅ Historique généré pour {$count} combinaisons item/serveur !");
            return;
        }

        $server  = $this->option('server');
        $servers = $server === 'all' ? DofusMarketService::SERVERS : [$server];

        foreach ($servers as $srv) {
            $this->info("🔄 Génération des prix pour {$srv}...");
            $count = $service->scrapeServer($srv);
            $this->info("✅ {$count} prix générés pour {$srv}");
        }

        $this->info('🎉 Terminé !');
    }
}
