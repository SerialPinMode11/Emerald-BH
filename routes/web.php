<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();

        return redirect()->route($user->role->dashboardRoute());
    })->name('dashboard');
});

require __DIR__.'/emerald.php';
require __DIR__.'/settings.php';
