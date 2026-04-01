<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeSkillController;
Route::apiResource('employee-skills', EmployeeSkillController::class);