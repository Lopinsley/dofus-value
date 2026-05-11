<?php

use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\AlertController;

Route::prefix('v1')->group(function () {
    // Items
    Route::get('/items',          [ItemController::class, 'index']);
    Route::get('/items/trending', [ItemController::class, 'trending']);
    Route::get('/items/craft',    [ItemController::class, 'craftOpportunities']);
    Route::get('/items/{slug}',   [ItemController::class, 'show']);

    // Alertes
    Route::get('/alerts',         [AlertController::class, 'index']);
    Route::post('/alerts',        [AlertController::class, 'store']);
    Route::delete('/alerts/{id}', [AlertController::class, 'destroy']);
});
