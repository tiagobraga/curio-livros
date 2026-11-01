<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SuggestedEditController;
use App\Http\Controllers\LibraryController;

// Página Inicial (redireciona para dashboard se autenticado, ou login se visitante)
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/register-library', [AuthController::class, 'showRegisterLibrary'])->name('register-library');
Route::post('/register-library', [AuthController::class, 'registerLibrary'])->name('register-library.post');

// Language Switcher Route
Route::get('/language/{locale}', [\App\Http\Controllers\LanguageController::class, 'switchLocale'])->name('language.switch');

// Password Reset Routes
Route::get('/forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions', [DashboardController::class, 'transactions'])->name('transactions.index');

    // Catalog Routes (Fase 3 & 6)
    Route::get('/catalog/suggested-edits', [SuggestedEditController::class, 'index'])->name('catalog.suggested_edits');
    Route::post('/catalog/suggested-edits/{id}/approve', [SuggestedEditController::class, 'approve'])->name('catalog.suggested_edits.approve');
    Route::post('/catalog/suggested-edits/{id}/reject', [SuggestedEditController::class, 'reject'])->name('catalog.suggested_edits.reject');

    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/catalog/create', [CatalogController::class, 'create'])->name('catalog.create');
    Route::post('/catalog', [CatalogController::class, 'store'])->name('catalog.store');
    Route::get('/catalog/lookup', [CatalogController::class, 'lookup'])->name('catalog.lookup');
    Route::get('/catalog/{id}/edit', [CatalogController::class, 'edit'])->name('catalog.edit');
    Route::put('/catalog/{id}', [CatalogController::class, 'update'])->name('catalog.update');
    Route::delete('/catalog/{id}', [CatalogController::class, 'destroy'])->name('catalog.destroy');
    Route::get('/catalog/{id}', [CatalogController::class, 'show'])->name('catalog.show');
    Route::post('/catalog/{id}/reviews', [CatalogController::class, 'storeReview'])->name('catalog.reviews.store');
    Route::delete('/reviews/{id}', [CatalogController::class, 'destroyReview'])->name('catalog.reviews.destroy');

    // Circulation Routes (Fase 4)
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::post('/loans/{id}/return', [LoanController::class, 'returnLoan'])->name('loans.return');

    // Profile Routes
    Route::get('/profile', [UserController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [UserController::class, 'updateProfilePassword'])->name('profile.update-password');
    Route::post('/profile/request-library', [UserController::class, 'requestLibraryLink'])->name('profile.request-library');

    // Users Routes (Fase 4)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::put('/users/{id}/password', [UserController::class, 'updatePassword'])->name('users.update-password');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/requests/{id}/approve', [UserController::class, 'approveRequest'])->name('users.requests.approve');
    Route::post('/users/requests/{id}/reject', [UserController::class, 'rejectRequest'])->name('users.requests.reject');

    // Libraries Routes (Fase 8)
    Route::get('/libraries', [LibraryController::class, 'index'])->name('libraries.index');
    Route::get('/libraries/create', [LibraryController::class, 'create'])->name('libraries.create');
    Route::post('/libraries', [LibraryController::class, 'store'])->name('libraries.store');
    Route::get('/libraries/{id}/edit', [LibraryController::class, 'edit'])->name('libraries.edit');
    Route::put('/libraries/{id}', [LibraryController::class, 'update'])->name('libraries.update');
    Route::delete('/libraries/{id}', [LibraryController::class, 'destroy'])->name('libraries.destroy');
    Route::get('/libraries/{id}/custom-fields', [LibraryController::class, 'getCustomFieldsAndFormat'])->name('libraries.custom_fields');
    Route::post('/libraries/{id}/recalculate-call-numbers', [LibraryController::class, 'recalculateCallNumbers'])->name('libraries.recalculate_call_numbers');
});

// Shared short URL route (public)
Route::get('/{code}', [CatalogController::class, 'sharedView'])
    ->name('catalog.shared')
    ->where('code', '^[A-Za-z0-9]{6}$');



