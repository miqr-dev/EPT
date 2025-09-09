<?php

namespace App\Services;

use App\Models\TestResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MrtPdfService
{
    public static function generate(TestResult $result): ?string
    {
        // Only attempt generation if DomPDF is installed
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return null;
        }

        $participantName = $result->assignment->participant->name ?? 'participant';
        $testName = $result->assignment->test->name ?? 'test';
        $durationSeconds = $result->result_json['total_time_seconds'] ?? 0;
        $durationFormatted = gmdate('i:s', $durationSeconds);

        $chartImage = ChartService::generate($result->result_json['group_stanines'] ?? []);

        $data = [
            'test_name' => $testName,
            'date' => now()->format('d.m.Y'),
            'participant_name' => $participantName,
            'duration' => $durationFormatted,
            'group_scores' => $result->result_json['group_scores'] ?? [],
            'group_stanines' => $result->result_json['group_stanines'] ?? [],
            'total_score' => $result->result_json['total_score'] ?? null,
            'prozentrang' => $result->result_json['prozentrang'] ?? null,
            'answers' => $result->result_json['answers'] ?? [],
            'chart_image' => $chartImage,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.mrt-result', $data)->setPaper('a4');
        $fileName = Str::slug($participantName, '_') . '_' . Str::slug($testName, '_') . '.pdf';
        $path = 'test_results/' . $fileName;
        Storage::put($path, $pdf->output());
        return $path;
    }
}
