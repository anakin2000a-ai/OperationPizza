<?php
use App\Http\Controllers\Api\ScoreCardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('stores/{store}/score-cards')->group(function () {
        Route::post('/create', [ScoreCardController::class, 'create']);
        
    });
});