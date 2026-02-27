<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserPdfController extends Controller
{
  public function show(Request $request)
  {
    $user = $request->user();

    $exam = Exam::whereHas('participants', function ($query) use ($user) {
      $query->where('participant_id', $user->id);
    })->latest('exams.created_at')->first();

    $canViewContract = $user->contract_view_enabled || ($exam?->contract_view_enabled ?? false);

    abort_unless($canViewContract, 403);

    $fileName = $this->resolvePdfPath($user);

    return Storage::disk('pdfs')->response($fileName, basename($fileName), ['Content-Type' => 'application/pdf']);
  }

  public function showParticipantPdf(Request $request, User $participant)
  {
    abort_unless(in_array($request->user()?->role, ['admin', 'teacher'], true), 403);

    $fileName = $this->resolvePdfPath($participant);

    return Storage::disk('pdfs')->response($fileName, basename($fileName), ['Content-Type' => 'application/pdf']);
  }

  private function resolvePdfPath(User $user): string
  {
    abort_unless($user->city_id, 404, 'Stadt ist dem Benutzer nicht zugewiesen.');

    $cityName = Str::of((string) optional($user->city)->name)
      ->trim()
      ->replace(' ', '_');

    abort_if($cityName->isEmpty(), 404, 'Stadt ist dem Benutzer nicht zugewiesen.');

    $base = Str::of($user->username)->trim();
    $disk = Storage::disk('pdfs');

    $fileName = collect([
      $cityName . '/' . $base . '.pdf',
      $cityName . '/' . $base . '.PDF',
    ])->first(fn($path) => $disk->exists($path));

    abort_unless($fileName, 404);

    return $fileName;
  }
}
