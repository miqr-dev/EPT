<?php

namespace App\Services;

use App\Models\TestResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LmtPdfService
{
    public static function generate(TestResult $result): ?string
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return null;
        }

        $assignment = $result->assignment;
        $participant = $assignment->participant;
        $participantName = $participant->name ?? 'participant';
        $testName = $assignment->test->name ?? 'test';
        $durationSeconds = $result->result_json['total_time_seconds'] ?? 0;
        $durationFormatted = gmdate('i:s', $durationSeconds);

        $data = [
            'test_name' => $testName,
            'date' => now()->format('d.m.Y'),
            'participant_name' => $participantName,
            'duration' => $durationFormatted,
            'group_scores' => $result->result_json['group_scores'] ?? [],
            'group_t_values' => $result->result_json['group_t_values'] ?? [],
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.lmt-result', $data)->setPaper('a4');
        $fileName = Str::slug($participantName, '_') . '_' . Str::slug($testName, '_') . '.pdf';
        $path = 'test_results/' . $fileName;
        Storage::put($path, $pdf->output());

        return $path;
    }
}
