<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoalController;

Route::middleware('fake.auth')->group(function () {
    Route::apiResource('goals', GoalController::class);
});
