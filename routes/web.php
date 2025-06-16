<?php

use App\Http\Controllers\CustomSettings\EinstellungenController;
use App\Http\Controllers\QuestionController; // Added this import
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
  return Inertia::render('Welcome');
})->name('home');

Route::get('tests', function () {
  return Inertia::render('TestsPage');
})->middleware(['auth', 'verified'])->name('tests');

Route::get('mrt', function () {
  return Inertia::render('MRT');
})->middleware(['auth', 'verified'])->name('mrt');

Route::get('dashboard', function () {
  return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Einstellungen (Custom Settings) Routes
Route::get('/einstellungen', [EinstellungenController::class, 'showEinstellungenPage'])
    ->middleware(['auth', 'verified'])
    ->name('einstellungen.index');

Route::get('/einstellungen/fragen', [EinstellungenController::class, 'showFragenPage'])
    ->middleware(['auth', 'verified'])
    ->name('einstellungen.fragen');

// CRUD operations for Questions under /einstellungen/fragen
Route::group(['prefix' => 'einstellungen', 'as' => 'einstellungen.', 'middleware' => ['auth', 'verified']], function () {
    // The GET route for /einstellungen/fragen is handled by EinstellungenController and is defined above.
    // This group handles the actions (POST, PUT, DELETE) for questions.
    // Note: Route model binding {question} will work as expected.
    Route::post('/fragen', [QuestionController::class, 'store'])->name('questions.store');
    Route::put('/fragen/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('/fragen/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
