<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ExamParticipant;
use App\Models\Exam;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnRole
{
  public function handle(Request $request, Closure $next): Response
  {
    if (
      $request->route()?->getName() &&
      in_array($request->route()->getName(), [
        'logout',
        'login',
        'password.request',
        'password.email',
        'password.reset',
        'participant.onboarding',
        'participant.onboarding.save',
        // ...any other auth routes
      ])
    ) {
      return $next($request);
    }

    if (Auth::check()) {
      $user = Auth::user();
      $currentRoute = $request->route()->getName();

      if ($user->role === 'participant' && !$user->can_login) {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
          'username' => __('Deine Anmeldung wurde deaktiviert. Bitte wende dich an deine Lehrkraft.'),
        ]);
      }

      // Allowed routes for participants
      $participantRoutes = [
        'participant',
        'participant.onboarding',
        'participant.onboarding.save',
        'logout',
        'my.pdf'
      ];

      // Allowed routes for teachers/admins
      $teacherAdminRoutes = [
        'dashboard',
        'participant',
        'participant.onboarding',
        'participant.onboarding.save',
        'mrt-a',
        'mrt-b',
        'brt-a',
        'brt-b',
        'bit-2',
        'fpi-r',
        'lmt',
        'lmt2',
        'lps-a',
        'lps-b',
        'avem',
        'assign.tests',
        'remove.tests',
        'exams',
        'settings',
        'appearance',
        'logout',
        'avem',
        'konzentrationstest',
        'api.active-exams',
        'exams.updateSteps',
        'participants.list',
        'participants.login-permission',
        'test-results.download',
        'test-results.pdf',
        'exam-step-status.add-time',
      ];

      if ($user->role === 'participant') {
        $profile = $user->participantProfile;
        $profileComplete = $profile && $profile->birthday && $profile->sex;

        $allowedRoutes = $participantRoutes;
        $examAccessible = false;
        if ($profileComplete) {
          $examParticipant = ExamParticipant::where('participant_id', $user->id)->first();
          $exam = $examParticipant?->exam;
          $examAccessible = $exam && in_array($exam->status, ['in_progress', 'paused', 'completed'], true);
          $allowedRoutes = array_merge($allowedRoutes, [
            'my-exam',
            'exam-room',
            'my-exam.start-step',
            'my-exam.complete-step',
            'participant.no-exam',
            'my-exam.pause-step'
          ]);
        }

        if (!in_array($currentRoute, $allowedRoutes)) {
          if (!$profileComplete) {
            return redirect()->route('participant.onboarding');
          }
          return redirect()->route($examAccessible ? 'my-exam' : 'participant.no-exam');
        }
      }

      // if (in_array($user->role, ['admin', 'teacher']) && !in_array($currentRoute, $teacherAdminRoutes)) {
      if (
        in_array($user->role, ['admin', 'teacher']) &&
        !in_array($currentRoute, $teacherAdminRoutes) &&
        !Str::startsWith($currentRoute, 'exams.')
      ) {
        return redirect()->route('dashboard');
      }
    }

    return $next($request);
  }
}
