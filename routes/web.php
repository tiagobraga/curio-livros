<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']); // Fallback route

    // Catalog Placeholders (Fase 3)
    Route::get('/catalog', function () {
        return view('catalog.index'); // We will build these views soon
    })->name('catalog.index');

    Route::get('/catalog/create', function () {
        return view('catalog.create');
    })->name('catalog.create');

    // Loans Placeholders (Fase 4)
    Route::get('/loans', function () {
        return "Página de Circulação (Em breve)";
    })->name('loans.index');

    // Users Placeholders (Fase 4)
    Route::get('/users', function () {
        return "Página de Usuários (Em breve)";
    })->name('users.index');
});
