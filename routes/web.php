<?php

use App\Http\Controllers\ExploreController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\TripBlockController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ExploreController::class, 'index'])->name('explore');

Route::get('/dashboard', fn () => redirect()->route('explore'))
    ->middleware(['auth'])
    ->name('dashboard');

Route::get('/t/{token}', [ShareController::class, 'show'])->name('share.show');
Route::post('/t/{token}/suggest', [TripBlockController::class, 'suggest'])
    ->middleware('throttle:10,1')
    ->name('share.suggest');

Route::get('/travel/{location}/links', [TravelController::class, 'links'])
    ->name('travel.links');

Route::middleware('auth')->group(function () {
    Route::get('/locations/create', [LocationController::class, 'create'])->name('locations.create');
    Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');

    Route::get('/trip', [TripController::class, 'show'])->name('trip.show');
    Route::get('/trip/setup', [TripController::class, 'setup'])->name('trip.setup');
    Route::post('/trip/setup', [TripController::class, 'saveSetup'])->name('trip.setup.save');

    Route::post('/trip/locations/{location}', [TripController::class, 'add'])->name('trip.add');
    Route::delete('/trip/locations/{location}', [TripController::class, 'remove'])->name('trip.remove');
    Route::patch('/trip/locations/{location}', [TripController::class, 'assignDay'])->name('trip.assign');
    Route::patch('/trip/costs', [TripController::class, 'updateCosts'])->name('trip.costs');
    Route::patch('/trip/reorder', [TripController::class, 'reorder'])->name('trip.reorder');

    Route::post('/trip/blocks', [TripBlockController::class, 'store'])->name('trip.blocks.store');
    Route::patch('/trip/blocks/{block}', [TripBlockController::class, 'update'])->name('trip.blocks.update');
    Route::delete('/trip/blocks/{block}', [TripBlockController::class, 'destroy'])->name('trip.blocks.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
