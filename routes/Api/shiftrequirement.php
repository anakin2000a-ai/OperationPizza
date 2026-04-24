<?php
use App\Http\Controllers\Api\ShiftRequirementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum',\App\Http\Middleware\CheckStoreAccess::class)->group(function () {
    Route::prefix('stores/{store}')->group(function () {
        Route::prefix('shift-requirements')->group(function () {
            Route::get('/', [ShiftRequirementController::class, 'indexByStore']);
            Route::post('/', [ShiftRequirementController::class, 'store']);
            Route::get('/{id}', [ShiftRequirementController::class, 'show']);
            Route::put('/{id}', [ShiftRequirementController::class, 'update']);
            Route::delete('/{id}', [ShiftRequirementController::class, 'destroy']);
        });
    });
}); 