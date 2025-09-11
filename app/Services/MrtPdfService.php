<?php

namespace App\Services;

use App\Models\TestResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MrtPdfService
{
    public static function generate(TestResult $result, ?string $chartImage = null): ?string
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return null;
        }

        $participantName = $result->assignment->participant->name ?? 'participant';
        $testName = $result->assignment->test->name ?? 'test';
        $json = $result->result_json ?? [];

        $data = [
            'test_name' => $testName,
            'date' => now()->format('d.m.Y'),
            'participant_name' => $participantName,
            'total_score' => $json['total_score'] ?? null,
            'total_time_seconds' => $json['total_time_seconds'] ?? null,
            'prozentrang' => $json['prozentrang'] ?? null,
            'group_scores' => $json['group_scores'] ?? [],
            'group_stanines' => $json['group_stanines'] ?? [],
            'answers' => self::formatAnswers($json['answers'] ?? []),
            'chart_image' => $chartImage,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.mrt-result', $data)->setPaper('a4');
        $fileName = Str::slug($participantName, '_') . '_' . Str::slug($testName, '_') . '.pdf';
        $path = 'test_results/' . $fileName;
        Storage::put($path, $pdf->output());
        return $path;
    }

    protected static function formatAnswers(array $answers): array
    {
        return array_map(function ($a) {
            $a['correct'] = isset($a['correct_answers']) ? implode(', ', $a['correct_answers']) : '';
            $a['time_formatted'] = isset($a['time_seconds']) ? self::formatTime($a['time_seconds']) : '–';
            return $a;
        }, $answers);
    }

    protected static function formatTime(?int $seconds): string
    {
        if ($seconds === null) {
            return '–';
        }
        $minutes = floor($seconds / 60);
        $secs = $seconds % 60;
        return sprintf('%02d:%02d', $minutes, $secs);
    }
}
