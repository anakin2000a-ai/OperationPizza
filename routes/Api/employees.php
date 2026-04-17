<?php
use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function () {

Route::prefix('stores/{store}')->group(function () {
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::put('/employees/{employee}', [EmployeeController::class, 'update']);
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::get('/employees/{employee}', [EmployeeController::class, 'show']);
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);
    Route::delete('/employees/{employee}/deductions',[EmployeeController::class, 'destroyDeductions']);
});
});