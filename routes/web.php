<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;

// Public Web Pages
Route::get('/', [DashboardController::class, 'landing'])->name('home');

// Authentication Views
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Dashboard Pages (require login)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/weather', [DashboardController::class, 'weather']);
    Route::get('/currency', [DashboardController::class, 'currency']);
    Route::get('/news', [DashboardController::class, 'news']);
    Route::get('/ports', [DashboardController::class, 'ports']);
    Route::get('/analytics', [DashboardController::class, 'analytics']);
    Route::get('/compare', [DashboardController::class, 'compare']);
    Route::get('/watchlist', [DashboardController::class, 'watchlist'])->name('watchlist');
});

// Protected Admin Pages
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [DashboardController::class, 'admin'])->name('admin');
});

