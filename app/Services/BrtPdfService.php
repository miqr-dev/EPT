<?php

namespace App\Services;

use App\Models\TestResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BrtPdfService
{
    public static function generate(TestResult $result): ?string
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return null;
        }

        $assignment = $result->assignment;
        $participant = $assignment->participant;
        $profile = $participant->participantProfile;

        $first = $profile->firstname ?? '';
        $last = $profile->name ?? '';
        $participantName = trim($first . ' ' . $last) ?: ($participant->name ?? 'participant');
        $birthDate = $profile->birthday ? Carbon::parse($profile->birthday)->format('d.m.Y') : null;
        $age = $profile->age ?? null;

        $testName = $assignment->test->name ?? 'test';
        $examDate = $result->created_at ? $result->created_at->format('d.m.Y') : now()->format('d.m.Y');
        $durationSeconds = $result->result_json['total_time_seconds'] ?? 0;
        $durationFormatted = gmdate('i:s', $durationSeconds);

        $data = [
            'test_name' => $testName,
            'participant_name' => $participantName,
            'birth_date' => $birthDate,
            'age' => $age,
            'exam_date' => $examDate,
            'duration' => $durationFormatted,
            'rohwert' => $result->result_json['rohwert'] ?? null,
            'prozentrang' => $result->result_json['prozentrang'] ?? null,
            'twert' => $result->result_json['twert'] ?? null,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.brt-result', $data)->setPaper('a4');
        $fileName = Str::slug($participantName, '_') . '_' . Str::slug($testName, '_') . '.pdf';
        $path = 'test_results/' . $fileName;
        Storage::put($path, $pdf->output());
        return $path;
    }
}

