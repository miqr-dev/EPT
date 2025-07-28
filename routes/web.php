<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route::middleware(['auth', 'verified', 'role.redirect'])->group(function () {
// All role-protected pages
Route::get('dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
Route::get('participant', fn() => Inertia::render('Participant'))->name('participant');
Route::get('mrt', fn() => Inertia::render('MRT'))->name('mrt');
Route::get('brt', fn() => Inertia::render('BRT'))->name('brt');
Route::get('fpi', fn() => Inertia::render('FPI'))->name('fpi');
Route::get('lmt', fn() => Inertia::render('LMT'))->name('lmt');
Route::get('lmt2', fn() => Inertia::render('LMT2'))->name('lmt2');

Route::get('/login', function () {
  return Inertia::render('auth/Login', [
    'canResetPassword' => Route::has('password.request'),
    'status' => session('status'),
  ]);
})->middleware('guest')->name('login');
Route::redirect('/', '/login');
Route::get('/home', fn() => redirect()->route('dashboard'))->name('home');

// });

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
