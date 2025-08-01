<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ExamStepStatusController;


Route::middleware(['auth', 'verified', 'role.redirect'])->group(function () {
  // All role-protected pages
  Route::get('dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
  Route::get('participant', [ParticipantController::class, 'showProfileForm'])->name('participant');
  Route::get('mrt', fn() => Inertia::render('MRT'))->name('mrt');
  Route::get('brt', fn() => Inertia::render('BRT'))->name('brt');
  Route::get('fpi', fn() => Inertia::render('FPI'))->name('fpi');
  Route::get('lmt', fn() => Inertia::render('LMT'))->name('lmt');
  Route::get('lmt2', fn() => Inertia::render('LMT2'))->name('lmt2');
  Route::get('dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
  Route::post('assign-tests', [TeacherController::class, 'assignTests'])->name('assign.tests');
  Route::post('remove-tests', [TeacherController::class, 'removeTests'])->name('remove.tests');
  Route::get('/onboarding', [ParticipantController::class, 'showProfileForm'])->name('participant.onboarding');
  Route::post('/onboarding', [ParticipantController::class, 'storeProfile'])->name('participant.onboarding.save');

  // Participant's exam room
  Route::get('/my-exam', [ParticipantController::class, 'myExam'])->name('my-exam');
  Route::post('/my-exam/start-step', [ParticipantController::class, 'startStep'])->name('my-exam.start-step');
    Route::post('/my-exam/complete-step', [ParticipantController::class, 'completeStep'])->name('my-exam.complete-step');

  // Exam management (teacher/admin only, add middleware if needed)
  Route::post('/exam-step-status/{status}/add-time', [ExamStepStatusController::class, 'addTime'])->name('exam-step-status.add-time');

  // Participants management (assign to exam)
  Route::post('exams/{exam}/participants', [ExamController::class, 'addParticipants'])
    ->name('exams.addParticipants');

  // Exam flow control
    Route::post('/exams/{exam}/start', [ExamController::class, 'start'])->name('exams.start');
    Route::post('/exams/{exam}/next-step', [ExamController::class, 'nextStep'])->name('exams.next-step');
    Route::post('/exams/{exam}/set-status', [ExamController::class, 'setStatus'])->name('exams.set-status');
    
    Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');


  Route::get('/login', function () {
    return Inertia::render('auth/Login', [
      'canResetPassword' => Route::has('password.request'),
      'status' => session('status'),
    ]);
  })->middleware('guest')->name('login');
  Route::redirect('/', '/login');
  Route::get('/home', fn() => redirect()->route('dashboard'))->name('home');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
