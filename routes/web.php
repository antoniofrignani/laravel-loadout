<?php

use App\Http\Controllers\Builder\CustomPackageController;
use App\Http\Controllers\Builder\LoadoutPackageController;
use App\Http\Controllers\Builder\PackageCatalogController;
use App\Http\Controllers\Builder\ReviewController;
use App\Http\Controllers\PackageSuggestionModerationController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');
Route::get('builder/packages', PackageCatalogController::class)->name('builder.packages');
Route::post('builder/packages', [LoadoutPackageController::class, 'store'])->name('builder.packages.store');
Route::post('builder/custom-packages', [CustomPackageController::class, 'store'])->name('builder.custom-packages.store');
Route::get('builder/review', ReviewController::class)->name('builder.review');
Route::get('package-suggestions/{package_suggestion}/approve', [PackageSuggestionModerationController::class, 'approve'])
    ->middleware('signed')
    ->name('package-suggestions.approve');
Route::get('package-suggestions/{package_suggestion}/ignore', [PackageSuggestionModerationController::class, 'ignore'])
    ->middleware('signed')
    ->name('package-suggestions.ignore');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
