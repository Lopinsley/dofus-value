<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\AlertController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Items
    Route::get('items', [ItemController::class, 'index']);
    Route::get('items/trending', [ItemController::class, 'trending']);
    Route::get('items/craft-opportunities', [ItemController::class, 'craftOpportunities']);
    Route::get('items/{slug}', [ItemController::class, 'show']);

    // Prices history
    Route::get('items/{slug}/prices', [ItemController::class, 'priceHistory']);

    // Alerts (auth required)
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('alerts', AlertController::class);
    });

});
