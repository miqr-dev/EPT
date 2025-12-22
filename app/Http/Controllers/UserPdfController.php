<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamParticipant;
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

    // e.g. "J.Doe"
    $base = Str::of($user->username)->trim();

    // "J.Doe.pdf"
    $fileName = $base . '.pdf';

    $disk = \Illuminate\Support\Facades\Storage::disk('windows_pdfs');

    $candidates = [$base . '.pdf', $base . '.PDF'];
    $fileName = collect($candidates)->first(fn($p) => $disk->exists($p));
    abort_unless($fileName, 404);
    return $disk->response($fileName, basename($fileName), ['Content-Type' => 'application/pdf']);
  }
}
