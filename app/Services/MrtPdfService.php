<?php

namespace App\Services;

use App\Models\TestResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MrtPdfService
{
    protected static function buildData(TestResult $result, ?string $chart = null): array
    {
        $assignment = $result->assignment;
        $participantName = $assignment->participant->name ?? 'participant';
        $testName = $assignment->test->name ?? 'test';
        $durationSeconds = $result->result_json['total_time_seconds'] ?? 0;
        $durationFormatted = gmdate('i:s', $durationSeconds);

        return [
            'test_name' => $testName,
            'date' => now()->format('d.m.Y'),
            'participant_name' => $participantName,
            'duration' => $durationFormatted,
            'total_score' => $result->result_json['total_score'] ?? null,
            'prozentrang' => $result->result_json['prozentrang'] ?? null,
            'group_scores' => $result->result_json['group_scores'] ?? [],
            'group_stanines' => $result->result_json['group_stanines'] ?? [],
            'chart' => $chart,
        ];
    }

    public static function generate(TestResult $result): ?string
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return null;
        }

        $data = self::buildData($result);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.mrt-result', $data)->setPaper('a4');
        $fileName = Str::slug($data['participant_name'], '_') . '_' . Str::slug($data['test_name'], '_') . '.pdf';
        $path = 'test_results/' . $fileName;
        Storage::put($path, $pdf->output());
        return $path;
    }

    public static function download(TestResult $result, string $chart)
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return null;
        }

        $data = self::buildData($result, $chart);
        $fileName = Str::slug($data['participant_name'], '_') . '_' . Str::slug($data['test_name'], '_') . '.pdf';
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.mrt-result', $data)
            ->setPaper('a4')
            ->download($fileName);
    }
}
