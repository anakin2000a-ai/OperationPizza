<?php

use App\Http\Controllers\Api\EmployeeByStoreController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('employees/by-store', [EmployeeByStoreController::class, 'employeesByStore']);
});