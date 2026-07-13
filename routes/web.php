<?php

use App\Http\Controllers\Builder\PackageCatalogController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');
Route::get('builder/packages', PackageCatalogController::class)->name('builder.packages');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
