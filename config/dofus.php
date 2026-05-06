<?php

return [
    'api' => [
        'base_url' => env('DOFUS_API_BASE_URL', 'https://api.dofusdu.de'),
        'game_version' => env('DOFUS_GAME_VERSION', 'dofus3'),
        'language' => env('DOFUS_LANGUAGE', 'fr'),
        'timeout' => 30,
        'retry' => 3,
    ],

    'scraping' => [
        'interval_minutes' => env('PRICE_SCRAPE_INTERVAL_MINUTES', 60),
        'history_days' => env('PRICE_HISTORY_DAYS', 90),
        'batch_size' => 50,
    ],

    'categories' => [
        'weapons' => 'Armes',
        'equipment' => 'Équipements',
        'consumables' => 'Consommables',
        'resources' => 'Ressources',
        'pets' => 'Familiers',
        'mounts' => 'Montures',
        'cosmetics' => 'Cosmétiques',
    ],

    'alerts' => [
        'discord_webhook' => env('DISCORD_WEBHOOK_URL'),
        'cooldown_hours' => 4,
    ],
];
