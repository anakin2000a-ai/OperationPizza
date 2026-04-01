<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SkillController;

Route::apiResource('skills', SkillController::class);