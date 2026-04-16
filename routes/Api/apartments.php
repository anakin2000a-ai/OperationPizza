
<?php

use App\Http\Controllers\Api\ApartmentController;
use Illuminate\Support\Facades\Route;
 

 Route::middleware(['auth:sanctum', \App\Http\Middleware\SeniorManagerMiddleware::class])->group(function () {
    Route::apiResource('apartments', ApartmentController::class);
    
    // Soft delete, restore, and force delete routes
    Route::post('apartments/{id}/restore', [ApartmentController::class, 'restore']);
    Route::delete('apartments/{id}/force-delete', [ApartmentController::class, 'forceDelete']);
});
