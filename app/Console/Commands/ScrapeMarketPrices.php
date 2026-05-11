<?php

namespace App\Console\Commands;

use App\Services\DofusMarketService;
use Illuminate\Console\Command;

class ScrapeMarketPrices extends Command
{
    protected $signature   = 'dofus:scrape {--server=all : Server to scrape (draconiros/ombre/hellmina/orukam/all)}';
    protected $description = 'Scrape Dofus HDV prices from dofusdu.de API';

    public function handle(DofusMarketService $service): void
    {
        $server = $this->option('server');
        $servers = $server === 'all' ? DofusMarketService::SERVERS : [$server];

        foreach ($servers as $srv) {
            $this->info("🔄 Scraping {$srv}...");
            $count = $service->scrapeServer($srv);
            $this->info("✅ {$count} prix récupérés pour {$srv}");
        }

        $this->info('🎉 Scraping terminé !');
    }
}
