<?php

use Illuminate\Support\Facades\Route;
use Subhashladumor1\Translate\Http\Controllers\DashboardController;

Route::middleware(['web'])->group(function () {
    Route::get('/translate/dashboard', [DashboardController::class, 'index'])
        ->name('translate.dashboard');
});
