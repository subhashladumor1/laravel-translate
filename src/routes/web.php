<?php

use Illuminate\Support\Facades\Route;
use Subhashladumor1\Translate\Http\Controllers\DashboardController;

Route::middleware(['web'])->group(function () {
    Route::get('/translate/dashboard', [DashboardController::class, 'index'])
        ->name('translate.dashboard');
    
    // Test page for Blade directives
    Route::get('/translate/test', function () {
        return view('translate::test-directives');
    })->name('translate.test');
});
