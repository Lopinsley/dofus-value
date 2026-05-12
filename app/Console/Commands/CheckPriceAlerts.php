<?php

namespace App\Console\Commands;

use App\Mail\PriceAlertTriggered;
use App\Models\PriceAlert;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckPriceAlerts extends Command
{
    protected $signature   = 'dofus:check-alerts';
    protected $description = 'Vérifie les alertes de prix et envoie les notifications email';

    public function handle(): void
    {
        $alerts = PriceAlert::with(['item'])
            ->where('triggered', false)
            ->get();

        $triggered = 0;

        foreach ($alerts as $alert) {
            $latest = $alert->item->latestPrice($alert->server);
            if (!$latest) continue;

            $currentPrice = $latest->price_1;
            $shouldTrigger = match($alert->direction) {
                'below' => $currentPrice <= $alert->threshold_price,
                'above'  => $currentPrice >= $alert->threshold_price,
                default  => false,
            };

            if ($shouldTrigger) {
                $alert->update([
                    'triggered'    => true,
                    'triggered_at' => Carbon::now(),
                ]);

                if ($alert->email) {
                    Mail::to($alert->email)->send(new PriceAlertTriggered($alert, $currentPrice));
                }

                $triggered++;
                $this->info("✅ Alerte #{$alert->id} déclenchée : {$alert->item->name} à {$currentPrice} kamas");
            }
        }

        $this->info("🔔 {$triggered} alerte(s) déclenchée(s) sur {$alerts->count()} vérifiées.");
    }
}
