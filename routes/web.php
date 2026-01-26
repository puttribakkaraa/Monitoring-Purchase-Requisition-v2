<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/import', [DashboardController::class, 'import'])->name('import');

// Mitigation API routes
Route::get('/pr/{id}/details', [DashboardController::class, 'getPrDetails'])->name('pr.details');
Route::post('/pr/{id}/mitigation', [DashboardController::class, 'updateMitigation'])->name('pr.mitigation');
Route::post('/pr/{id}/comment', [DashboardController::class, 'addComment'])->name('pr.comment');
Route::get('/pr/{id}/comments', [DashboardController::class, 'getComments'])->name('pr.comments');

// Requestor (User View) Routes
Route::prefix('requestor')->name('requestor.')->group(function () {
    Route::get('/login', [App\Http\Controllers\RequestorController::class, 'login'])->name('login');
    Route::post('/login', [App\Http\Controllers\RequestorController::class, 'authenticate'])->name('auth');
    Route::get('/dashboard', [App\Http\Controllers\RequestorController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [App\Http\Controllers\RequestorController::class, 'logout'])->name('logout');
});
