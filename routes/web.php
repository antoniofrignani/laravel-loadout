<?php

use App\Http\Controllers\Builder\CustomPackageController;
use App\Http\Controllers\Builder\DownloadController;
use App\Http\Controllers\Builder\LoadoutPackageController;
use App\Http\Controllers\Builder\PackageCatalogController;
use App\Http\Controllers\Builder\ProjectBasicsController;
use App\Http\Controllers\Builder\PublishController;
use App\Http\Controllers\Builder\ReviewController;
use App\Http\Controllers\Builder\RuntimeController;
use App\Http\Controllers\Builder\StarterKitController;
use App\Http\Controllers\LoadoutDownloadController;
use App\Http\Controllers\LoadoutShowController;
use App\Http\Controllers\PackageSuggestionModerationController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');
Route::get('builder/project', [ProjectBasicsController::class, 'show'])->name('builder.project');
Route::put('builder/project', [ProjectBasicsController::class, 'update'])->name('builder.project.update');
Route::get('builder/starter', [StarterKitController::class, 'show'])->name('builder.starter');
Route::put('builder/starter', [StarterKitController::class, 'update'])->name('builder.starter.update');
Route::get('builder/runtime', [RuntimeController::class, 'show'])->name('builder.runtime');
Route::put('builder/runtime', [RuntimeController::class, 'update'])->name('builder.runtime.update');
Route::get('builder/packages', PackageCatalogController::class)->name('builder.packages');
Route::post('builder/packages', [LoadoutPackageController::class, 'store'])->name('builder.packages.store');
Route::patch('builder/packages/{loadout_package}', [LoadoutPackageController::class, 'update'])->name('builder.packages.update');
Route::post('builder/custom-packages', [CustomPackageController::class, 'store'])->name('builder.custom-packages.store');
Route::get('builder/review', ReviewController::class)->name('builder.review');
Route::get('builder/download', DownloadController::class)->name('builder.download');
Route::post('builder/publish', PublishController::class)->name('builder.publish');
Route::get('loadouts/{loadout:slug}/download', LoadoutDownloadController::class)->name('loadouts.download');
Route::get('loadouts/{loadout:slug}', LoadoutShowController::class)->name('loadouts.show');
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
