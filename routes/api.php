
use App\Http\Controllers\Api\ItemController;

Route::prefix('v1')->group(function () {
    Route::get('/items',                [ItemController::class, 'index']);
    Route::get('/items/trending',       [ItemController::class, 'trending']);
    Route::get('/items/craft',          [ItemController::class, 'craftOpportunities']);
    Route::get('/items/{slug}',         [ItemController::class, 'show']);
});
