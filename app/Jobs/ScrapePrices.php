<?php

namespace App\Jobs;

use App\Models\Item;
use App\Models\Price;
use App\Services\DofusApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScrapePrices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 3;

    public function __construct(
        private readonly string $server = 'all',
        private readonly ?int $itemId = null
    ) {}

    public function handle(DofusApiService $dofusApi): void
    {
        Log::info('ScrapePrices: Starting', [
            'server' => $this->server,
            'item_id' => $this->itemId,
        ]);

        $query = Item::query();
        if ($this->itemId) {
            $query->where('id', $this->itemId);
        }

        $query->chunk(config('dofus.scraping.batch_size', 50), function ($items) use ($dofusApi) {
            foreach ($items as $item) {
                $this->scrapeItem($item, $dofusApi);
                // Rate limiting : respecter l'API Ankama
                usleep(200000); // 200ms entre chaque requête
            }
        });

        Log::info('ScrapePrices: Completed', ['server' => $this->server]);
    }

    private function scrapeItem(Item $item, DofusApiService $dofusApi): void
    {
        try {
            // Note: L'API dofusdu.de fournit des prix HDV
            // En production, on enrichit avec des données scraping
            $data = $dofusApi->getItem($item->dofus_id);

            if (!$data) return;

            // Extraction des prix depuis la réponse API
            $prices = $data['prices'] ?? null;
            if (!$prices) return;

            Price::create([
                'item_id' => $item->id,
                'server' => $this->server,
                'price_1' => $prices['one'] ?? null,
                'price_10' => $prices['ten'] ?? null,
                'price_100' => $prices['hundred'] ?? null,
                'volume' => $prices['volume'] ?? null,
                'recorded_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::warning('ScrapePrices: Item failed', [
                'item_id' => $item->id,
                'dofus_id' => $item->dofus_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
