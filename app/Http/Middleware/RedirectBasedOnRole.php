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
        // ...any other auth routes
      ])
    ) {
      return $next($request);
    }

    $user = Auth::user();
    if ($user) {
      if ($user->role === 'participant' && $request->route()->getName() !== 'participant') {
        return redirect()->route('participant');
      }
      if (in_array($user->role, ['admin', 'teacher']) && $request->route()->getName() !== 'dashboard') {
        return redirect()->route('dashboard');
      }
    }
    return $next($request);
  }
}
