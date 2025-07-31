<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        'logout'
      ];

      if ($user->role === 'participant' && !in_array($currentRoute, $participantRoutes)) {
        return redirect()->route('participant');
      }

      if (in_array($user->role, ['admin', 'teacher']) && !in_array($currentRoute, $teacherAdminRoutes)) {
        return redirect()->route('dashboard');
      }
    }

    return $next($request);
  }
}