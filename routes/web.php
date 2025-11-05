<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ExamStepStatusController;
use App\Http\Controllers\TestResultController;

Route::middleware(['auth', 'verified', 'role.redirect'])->group(function () {
    // All role-protected pages
    Route::get('dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
    Route::get('participant', [ParticipantController::class, 'showProfileForm'])->name('participant');
    Route::get('mrt-a', fn () => Inertia::render('MRT-A'))->name('mrt-a');
    Route::get('mrt-b', fn () => Inertia::render('MRT-B'))->name('mrt-b');
    Route::get('brt-a', fn () => Inertia::render('BRT-A'))->name('brt-a');
    Route::get('brt-b', fn () => Inertia::render('BRT-B'))->name('brt-b');
    Route::get('fpi-r', fn () => Inertia::render('FPI-R'))->name('fpi-r');
    Route::get('lmt', fn () => Inertia::render('LMT'))->name('lmt');
    Route::get('lmt2', fn () => Inertia::render('LMT2'))->name('lmt2');
    Route::get('bit-2', fn () => Inertia::render('BIT-2'))->name('bit-2');
    Route::get('avem', fn () => Inertia::render('AVEM'))->name('avem');
    Route::get('konzentrationstest', fn () => Inertia::render('Konzentrationstest'))->name('konzentrationstest');
    Route::post('assign-tests', [TeacherController::class, 'assignTests'])->name('assign.tests');
    Route::post('remove-tests', [TeacherController::class, 'removeTests'])->name('remove.tests');
    Route::get('/onboarding', [ParticipantController::class, 'showProfileForm'])->name('participant.onboarding');
    Route::post('/onboarding', [ParticipantController::class, 'storeProfile'])->name('participant.onboarding.save');

    // Participant's exam room
    Route::get('/my-exam', [ParticipantController::class, 'examLauncher'])->name('my-exam');
    Route::get('/no-exam', [ParticipantController::class, 'noExam'])->name('participant.no-exam');
    Route::post('/my-exam/start-step', [ParticipantController::class, 'startStep'])->name('my-exam.start-step');
    Route::post('/my-exam/complete-step', [ParticipantController::class, 'completeStep'])->name('my-exam.complete-step');
    Route::post('/my-exam/break-step', [ParticipantController::class, 'breakStep'])->name('my-exam.break-step');
    Route::post('/my-exam/save-progress', [ParticipantController::class, 'saveProgress'])->name('my-exam.save-progress');

    // Exam management (teacher/admin only, add middleware if needed)
    Route::post('/exam-step-status/{status}/add-time', [ExamStepStatusController::class, 'addTime'])->name('exam-step-status.add-time');

    // Participants management (assign to exam)
    Route::post('exams/{exam}/participants', [ExamController::class, 'addParticipants'])
        ->name('exams.addParticipants');

    // Exam flow control
    Route::post('/exams/{exam}/start', [ExamController::class, 'start'])->name('exams.start');
    Route::post('/exams/{exam}/next-step', [ExamController::class, 'nextStep'])->name('exams.next-step');
    Route::post('/exams/{exam}/set-status', [ExamController::class, 'setStatus'])->name('exams.set-status');
    Route::post('/exams/{exam}/set-step', [ExamController::class, 'setStep'])->name('exams.set-step');
    Route::post('/exams/{exam}/participants/{participant}/step-status', [ExamController::class, 'setParticipantStepStatus'])
        ->name('exams.participants.set-step-status');

    Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
    Route::post('/exams/store-with-participants', [ExamController::class, 'storeWithParticipants'])->name('exams.storeWithParticipants');
    Route::put('/exams/{exam}/steps', [ExamController::class, 'updateSteps'])->name('exams.updateSteps');
    Route::get('/api/active-exams', [ExamController::class, 'getActiveExams'])->name('api.active-exams');
    Route::get('/participants', [ParticipantController::class, 'list'])->name('participants.list');
    Route::put('/test-results/{testResult}', [TestResultController::class, 'update'])->name('test-results.update');
});

Route::get('/login', function () {
    return Inertia::render('auth/Login', [
        'canResetPassword' => Route::has('password.request'),
        'status' => session('status'),
    ]);
})->middleware('guest')->name('login');
Route::redirect('/', '/login');
Route::get('/home', fn () => redirect()->route('dashboard'))->name('home');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
