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

      // Allowed routes for participants
      $participantRoutes = [
        'participant',
        'participant.onboarding',
        'participant.onboarding.save',
        'logout'
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
        'test-results.download',
        'test-results.pdf',
        'exam-step-status.add-time',
      ];

      if ($user->role === 'participant') {
        $profile = $user->participantProfile;
        $profileComplete = $profile && $profile->birthday && $profile->sex;

        $allowedRoutes = $participantRoutes;
        $examStarted = false;
        if ($profileComplete) {
          $examParticipant = ExamParticipant::where('participant_id', $user->id)->first();
          $exam = $examParticipant?->exam;
          $examStarted = $exam && $exam->status === 'in_progress';
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
          return redirect()->route($examStarted ? 'my-exam' : 'participant.no-exam');
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
