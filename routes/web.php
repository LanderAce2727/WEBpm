<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('messenger');
});

Route::get('/dashboard', function () {
    return view('messenger');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/profile', function () {
    return view('profile');
})->name('profile');

Route::get('/settings', function () {
    return view('settings');
})->name('settings');

Route::middleware('auth')->group(function () {
    Route::get('/account', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
