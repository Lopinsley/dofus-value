<?php

use Illuminate\Support\Facades\Schedule;

// Génération des prix toutes les heures
Schedule::command('dofus:scrape --server=all')->hourly();

// Vérification des alertes toutes les heures (après le scraping)
Schedule::command('dofus:check-alerts')->hourlyAt(5);
