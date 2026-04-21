<?php
use App\Http\Controllers\Api\SimController;
use Illuminate\Support\Facades\Route;
Route::middleware([
    'auth:sanctum',
    \App\Http\Middleware\SeniorManagerMiddleware::class,
    'throttle:60,1',
])->prefix('sims')->group(function () {
    Route::get('/', [SimController::class, 'index']);
    Route::post('/', [SimController::class, 'store']);
    Route::put('/{id}', [SimController::class, 'update']);
    Route::delete('/{id}', [SimController::class, 'destroy']);
    Route::delete('/{id}/force-delete', [SimController::class, 'forceDelete']);
    Route::post('/{id}/restore', [SimController::class, 'restore']);
});