<?php

use App\Http\Controllers\LeagueSimulatorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LeagueSimulatorController::class, 'teams'])->name('simulator.teams');
Route::post('/fixtures/generate', [LeagueSimulatorController::class, 'generateFixtures'])->name('simulator.generate-fixtures');
Route::get('/fixtures', [LeagueSimulatorController::class, 'fixtures'])->name('simulator.fixtures');
Route::get('/simulation', [LeagueSimulatorController::class, 'simulation'])->name('simulator.simulation');
Route::post('/simulation/play-next-week', [LeagueSimulatorController::class, 'playNextWeek'])->name('simulator.play-next-week');
Route::post('/simulation/play-all-weeks', [LeagueSimulatorController::class, 'playAllWeeks'])->name('simulator.play-all-weeks');
Route::post('/simulation/reset-data', [LeagueSimulatorController::class, 'resetData'])->name('simulator.reset-data');
