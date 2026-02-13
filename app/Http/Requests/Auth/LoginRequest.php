<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Throwable;

class LoginRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ];
  }
  public function authenticate(): void
  {
    $this->ensureIsNotRateLimited();

    $username = trim((string) $this->input('username'));
    $localUser = User::whereRaw('LOWER(username) = ?', [mb_strtolower($username)])->first();

    // Import allowlist applies only to LDAP participants.
    // Teachers/Admins can authenticate without manual pre-import.
    if (!$localUser) {
      $ldapRole = $this->resolveLdapRoleByUsername($username);

      if ($ldapRole === null || $ldapRole === 'participant') {
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
          'username' => __('Dieses Konto ist nicht freigeschaltet. Bitte wende dich an deine Lehrkraft.'),
        ]);
      }
    }

    $credentials = [
      'samaccountname' => $username,
      'password' => $this->input('password'),
    ];

    if (! Auth::attempt($credentials, $this->boolean('remember'))) {
      RateLimiter::hit($this->throttleKey());

      throw ValidationException::withMessages([
        'username' => trans('auth.failed'),
      ]);
    }

    RateLimiter::clear($this->throttleKey());
  }


  protected function resolveLdapRoleByUsername(string $username): ?string
  {
    try {
      $ldapUser = LdapUser::query()
        ->whereEquals('samaccountname', $username)
        ->first();

      if (!$ldapUser) {
        return null;
      }

      $dn = (string) $ldapUser->getDn();
      preg_match_all('/OU=([^,]+)/i', $dn, $ouMatches);
      $ous = $ouMatches[1] ?? [];

      foreach ($ous as $ou) {
        if (stripos($ou, 'EDV') !== false) {
          return 'admin';
        }
      }

      foreach ($ous as $ou) {
        if (stripos($ou, 'Verwaltung') !== false || stripos($ou, 'Mitarbeiter') !== false) {
          return 'teacher';
        }
      }

      return 'participant';
    } catch (Throwable) {
      return null;
    }
  }

  public function ensureIsNotRateLimited(): void
  {
    if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
      return;
    }

    event(new Lockout($this));

    $seconds = RateLimiter::availableIn($this->throttleKey());

    throw ValidationException::withMessages([
      'username' => trans('auth.throttle', [
        'seconds' => $seconds,
        'minutes' => ceil($seconds / 60),
      ]),
    ]);
  }

  /**
   * Get the rate limiting throttle key for the request.
   */
  public function throttleKey(): string
  {
    return Str::transliterate(Str::lower($this->string('username')) . '|' . $this->ip());
  }
}
