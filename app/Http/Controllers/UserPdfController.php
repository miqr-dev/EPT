<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamParticipant;
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

    $fileName = $this->resolvePdfPath($user->username);

    return Storage::disk('pdfs')->response($fileName, basename($fileName), ['Content-Type' => 'application/pdf']);
  }

  public function showParticipantPdf(Request $request, User $participant)
  {
    abort_unless(in_array($request->user()?->role, ['admin', 'teacher'], true), 403);

    $fileName = $this->resolvePdfPath($participant->username);

    return Storage::disk('pdfs')->response($fileName, basename($fileName), ['Content-Type' => 'application/pdf']);
  }

  private function resolvePdfPath(string $username): string
  {
    $base = Str::of($username)->trim();
    $disk = Storage::disk('pdfs');

    $fileName = collect([$base . '.pdf', $base . '.PDF'])->first(fn($path) => $disk->exists($path));

    abort_unless($fileName, 404);

    return $fileName;
  }
}
