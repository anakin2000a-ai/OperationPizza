<?php
use App\Http\Controllers\Api\EmployeeLoanController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum',\App\Http\Middleware\SeniorManagerMiddleware::class])->group(function () {

    Route::get('employee-loans', [EmployeeLoanController::class, 'index']);
    Route::post('employee-loans', [EmployeeLoanController::class, 'store']);
    Route::get('employee-loans/{id}', [EmployeeLoanController::class, 'show']);
    Route::delete('employee-loans/{id}', [EmployeeLoanController::class, 'destroy']);

});