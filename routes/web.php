<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']); // Fallback route

    // Catalog Routes (Fase 3)
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/catalog/create', [CatalogController::class, 'create'])->name('catalog.create');
    Route::post('/catalog', [CatalogController::class, 'store'])->name('catalog.store');
    Route::get('/catalog/lookup', [CatalogController::class, 'lookup'])->name('catalog.lookup');
    Route::get('/catalog/{id}', [CatalogController::class, 'show'])->name('catalog.show');

    // Loans Placeholders (Fase 4)
    Route::get('/loans', function () {
        return "Página de Circulação (Em breve)";
    })->name('loans.index');

    // Users Placeholders (Fase 4)
    Route::get('/users', function () {
        return "Página de Usuários (Em breve)";
    })->name('users.index');
});
