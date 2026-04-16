<?php
use App\Http\Controllers\Api\SimController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', \App\Http\Middleware\SeniorManagerMiddleware::class])->group(function () {

    Route::prefix('sim')->group(function () {
        // Store a new SIM card
        Route::post('/', [SimController::class, 'store']);
        
        // Update an existing SIM card
        Route::put('{id}', [SimController::class, 'update']);  // Use PATCH for partial updates
        
        // Get all SIM cards
        Route::get('/', [SimController::class, 'index']);
         
        // Soft delete a SIM card
        Route::delete('{id}', [SimController::class, 'destroy']);
        
        // Force delete a SIM card
        Route::delete('{id}/force-delete', [SimController::class, 'forceDelete']);
        
        // Restore a soft-deleted SIM card
        Route::post('{id}/restore', [SimController::class, 'restore']);
    });
});