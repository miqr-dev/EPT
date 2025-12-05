<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Handles the serving of user-specific PDF files.
 */
class UserPdfController extends Controller
{
    /**
     * Display the specified PDF for the user.
     *
     * This method checks if the user is authorized to view the contract PDF for their
     * exam and serves the file if it exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function show(Request $request): StreamedResponse
    {
        $user = $request->user();

        $examParticipant = ExamParticipant::where('participant_id', $user->id)->first();
        $exam = $examParticipant ? Exam::find($examParticipant->exam_id) : null;

        abort_unless($exam && $exam->contract_view_enabled, 403);

        // e.g. "J.Doe"
        $base = Str::of($user->username)->trim();

        // "J.Doe.pdf"
        $fileName = $base . '.pdf';

        $disk = Storage::disk('windows_pdfs');

        $candidates = [$base . '.pdf', $base . '.PDF'];
        $fileName = collect($candidates)->first(fn($p) => $disk->exists($p));
        abort_unless($fileName, 404);
        return $disk->response($fileName, basename($fileName), ['Content-Type' => 'application/pdf']);
    }
}
