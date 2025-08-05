<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ExamParticipant;
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
        'mrt',
        'brt',
        'fpi',
        'lmt',
        'lmt2',
        'assign.tests',
        'remove.tests',
        'exams',
        'settings',
        'appearance',
        'logout'
      ];

      if ($user->role === 'participant') {
        $profile = $user->participantProfile;
        $profileComplete = $profile && $profile->birthday && $profile->sex && $profile->marital_status && $profile->household;

        $allowedRoutes = $participantRoutes;
        if ($profileComplete) {
          $hasExam = ExamParticipant::where('participant_id', $user->id)->exists();
          $allowedRoutes = array_merge($allowedRoutes, ['my-exam', 'exam-room', 'my-exam.start-step', 'my-exam.complete-step']);
          if (!$hasExam) {
            $allowedRoutes[] = 'participant.no-exam';
          }
        }

        if (!in_array($currentRoute, $allowedRoutes)) {
          if (!$profileComplete) {
            return redirect()->route('participant.onboarding');
          } else {
            $hasExam = ExamParticipant::where('participant_id', $user->id)->exists();
            return redirect()->route($hasExam ? 'my-exam' : 'participant.no-exam');
          }
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
