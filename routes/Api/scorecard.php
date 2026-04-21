<?php
use App\Http\Controllers\Api\ScoreCardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('stores/{store}/score-cards')->group(function () {
        Route::post('/create', [ScoreCardController::class, 'create']);
        Route::get('/', [ScoreCardController::class, 'index']);
        Route::get('/{id}', [ScoreCardController::class, 'show']);

        Route::delete('/{id}', [ScoreCardController::class, 'softDelete']);
        Route::delete('/force/{id}', [ScoreCardController::class, 'forceDelete']);
        Route::patch('/restore/{id}', [ScoreCardController::class, 'restore']);
        
    });
});