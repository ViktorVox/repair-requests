<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceRequestController;
use Illuminate\Support\Facades\Route;

// Публичные роуты
Route::get('/', [ServiceRequestController::class, 'create'])->name('home');
Route::post('/requests', [ServiceRequestController::class, 'store'])->name('requests.store');

// Защищенные роуты
Route::middleware(['auth', 'verified'])->group(function () {
    // Дашборд с заявками
    Route::get('/dashboard', [ServiceRequestController::class, 'index'])->name('dashboard');
    
    // Взять в работу (защита от гонки)
    Route::post('/requests/{serviceRequest}/take', [ServiceRequestController::class, 'takeToWork'])->name('requests.take');
    
    // Смена статуса
    Route::patch('/requests/{serviceRequest}/status', [ServiceRequestController::class, 'updateStatus'])->name('requests.update-status');

    // Стандартные роуты профиля от Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';