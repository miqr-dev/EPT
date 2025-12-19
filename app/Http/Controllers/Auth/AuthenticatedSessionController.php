<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use LdapRecord\Laravel\Auth\ListensForLdapBindFailure;
use App\Models\Exam;
use App\Models\ExamParticipant;

class AuthenticatedSessionController extends Controller
{
  use ListensForLdapBindFailure;
  /**
   * Show the login page.
   */
  public function __construct()
  {
    $this->listenForLdapBindFailure();
  }
  public function create(Request $request): Response
  {
    return Inertia::render('auth/Login', [
      'canResetPassword' => Route::has('password.request'),
      'status' => $request->session()->get('status'),
    ]);
  }

  /**
   * Handle an incoming authentication request.
   */
  public function store(LoginRequest $request): RedirectResponse
  {
    $request->authenticate();

    $request->session()->regenerate();

    $user = $request->user();
    if (!$user->can_login) {
      Auth::guard('web')->logout();

      $request->session()->invalidate();
      $request->session()->regenerateToken();

      return redirect()->route('login')->withErrors([
        'username' => __('Deine Anmeldung wurde deaktiviert. Bitte wende dich an deine Lehrkraft.'),
      ]);
    }
    if ($user->role === 'participant') {
      $profile = $user->participantProfile;
      $profileComplete = $profile && $profile->birthday && $profile->sex;
      if (!$profileComplete) {
        return redirect()->intended(route('participant.onboarding', absolute: false));
      }
      $examParticipant = ExamParticipant::where('participant_id', $user->id)->first();
      if (!$examParticipant) {
        return redirect()->intended(route('participant.no-exam', absolute: false));
      }
      $exam = Exam::find($examParticipant->exam_id);
      if ($exam && in_array($exam->status, ['in_progress', 'paused', 'completed'], true)) {
        return redirect()->intended(route('my-exam', absolute: false));
      }
      return redirect()->intended(route('participant.no-exam', absolute: false));
    }

    return redirect()->intended(route('dashboard', absolute: false));
  }

  /**
   * Destroy an authenticated session.
   */
  public function destroy(Request $request): RedirectResponse
  {
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
  }
}
