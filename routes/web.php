<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;

// Auth Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Admin Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tv', [DashboardController::class, 'tvDashboard'])->name('tv.dashboard');
    Route::post('/import', [DashboardController::class, 'import'])->name('import');

    // Mitigation API routes
    Route::get('/pr/{id}/details', [DashboardController::class, 'getPrDetails'])->name('pr.details');
    Route::post('/pr/{id}/mitigation', [DashboardController::class, 'updateMitigation'])->name('pr.mitigation');
    Route::post('/pr/{id}/comment', [DashboardController::class, 'addComment'])->name('pr.comment');
    Route::get('/pr/{id}/comments', [DashboardController::class, 'getComments'])->name('pr.comments');
    Route::post('/pr/{id}/convert', [DashboardController::class, 'convertToPo'])->name('pr.convert');
    Route::post('/pr/{id}/ask-feedback', [DashboardController::class, 'askFeedback'])->name('pr.askFeedback');
    Route::get('/api/timeline-data', [DashboardController::class, 'getTimelineData'])->name('api.timeline');
    Route::get('/api/smart-cards', [DashboardController::class, 'getSmartCardData'])->name('api.smartcards');
    Route::post('/api/department/pic', [DashboardController::class, 'updateDepartmentPic'])->name('api.updatePic');
    Route::post('/notifications/mark-all-read', [DashboardController::class, 'markAllRead'])->name('notifications.markAllRead');

    // User Management
    Route::get('/users', [AuthController::class, 'users'])->name('users.index');
    Route::post('/users', [AuthController::class, 'storeUser'])->name('users.store');
    Route::delete('/users/{id}', [AuthController::class, 'deleteUser'])->name('users.delete');
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
});

// Requestor (User View) Routes - No auth required
Route::prefix('requestor')->name('requestor.')->group(function () {
    Route::get('/login', [App\Http\Controllers\RequestorController::class, 'login'])->name('login');
    Route::post('/login', [App\Http\Controllers\RequestorController::class, 'authenticate'])->name('auth');
    Route::get('/dashboard', [App\Http\Controllers\RequestorController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [App\Http\Controllers\RequestorController::class, 'logout'])->name('logout');
    Route::get('/pr/{id}/details', [App\Http\Controllers\RequestorController::class, 'getPrDetails'])->name('pr.details');
    Route::post('/pr/{id}/respond-feedback', [App\Http\Controllers\RequestorController::class, 'respondFeedback'])->name('pr.respondFeedback');
    Route::post('/pr/{id}/comment', [DashboardController::class, 'addComment'])->name('pr.comment');
});
