<?php

use App\Http\Controllers\LeagueSimulatorController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LeagueSimulatorController::class, 'teams'])->name('simulator.teams');
Route::post('/fixtures/generate', [LeagueSimulatorController::class, 'generateFixtures'])->name('simulator.generate-fixtures');
Route::get('/fixtures', [LeagueSimulatorController::class, 'fixtures'])->name('simulator.fixtures');
Route::get('/simulation', [LeagueSimulatorController::class, 'simulation'])->name('simulator.simulation');
Route::post('/simulation/play-next-week', [LeagueSimulatorController::class, 'playNextWeek'])->name('simulator.play-next-week');
Route::post('/simulation/play-all-weeks', [LeagueSimulatorController::class, 'playAllWeeks'])->name('simulator.play-all-weeks');
Route::post('/simulation/reset-data', [LeagueSimulatorController::class, 'resetData'])->name('simulator.reset-data');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [LeagueSimulatorController::class, 'teams'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
