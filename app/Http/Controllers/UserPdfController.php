<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserPdfController extends Controller
{
  public function show(Request $request)
  {
    $user = $request->user();

    // e.g. "J.Doe"
    $base = \Illuminate\Support\Str::of($user->username)->trim();

    // "J.Doe.pdf"
    $fileName = $base . '.pdf';

    $disk = \Illuminate\Support\Facades\Storage::disk('windows_pdfs');

    $candidates = [$base . '.pdf', $base . '.PDF'];
    $fileName = collect($candidates)->first(fn($p) => $disk->exists($p));
    abort_unless($fileName, 404);
    return $disk->response($fileName, basename($fileName), ['Content-Type' => 'application/pdf']);
  }
}
