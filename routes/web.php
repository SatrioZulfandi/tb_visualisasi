<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InsightController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');


// Insights
Route::get('/insights', [InsightController::class, 'index'])->name('insights');

