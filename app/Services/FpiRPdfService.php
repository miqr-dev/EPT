<?php

namespace App\Services;

use App\Models\TestResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FpiRPdfService
{
    protected static array $categories = [
        ['label' => 'Lebenszufriedenheit', 'score_key' => 1, 'stanine_key' => 'LEB'],
        ['label' => 'Soziale Orientierung', 'score_key' => 2, 'stanine_key' => 'SOZ'],
        ['label' => 'Leistungsorientierung', 'score_key' => 3, 'stanine_key' => 'LEI'],
        ['label' => 'Gehemmtheit', 'score_key' => 4, 'stanine_key' => 'GEH'],
        ['label' => 'Erregbarkeit', 'score_key' => 5, 'stanine_key' => 'ERR'],
        ['label' => 'Aggressivität', 'score_key' => 6, 'stanine_key' => 'AGGR'],
        ['label' => 'Beanspruchung', 'score_key' => 7, 'stanine_key' => 'BEAN'],
        ['label' => 'Körperliche Beschwerden', 'score_key' => 8, 'stanine_key' => 'KORP'],
        ['label' => 'Gesundheitssorgen', 'score_key' => 9, 'stanine_key' => 'GES'],
        ['label' => 'Offenheit', 'score_key' => 10, 'stanine_key' => 'OFF'],
        ['label' => 'Extraversion', 'score_key' => 'E', 'stanine_key' => 'EXTR'],
        ['label' => 'Emotionalität', 'score_key' => 'N', 'stanine_key' => 'EMOT'],
    ];

    public static function generate(TestResult $result): ?string
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return null;
        }

        $participantName = $result->assignment->participant->name ?? 'participant';
        $testName = $result->assignment->test->name ?? 'test';
        $durationSeconds = $result->result_json['total_time_seconds'] ?? 0;
        $durationFormatted = gmdate('i:s', $durationSeconds);

        $categories = [];
        foreach (self::$categories as $cat) {
            $categories[] = [
                'label' => $cat['label'],
                'raw' => $result->result_json['category_scores'][$cat['score_key']] ?? null,
                'stanine' => $result->result_json['category_stanines'][$cat['stanine_key']] ?? null,
            ];
        }

        $data = [
            'test_name' => $testName,
            'date' => now()->format('d.m.Y'),
            'participant_name' => $participantName,
            'duration' => $durationFormatted,
            'categories' => $categories,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.fpi-r-result', $data)->setPaper('a4');
        $fileName = Str::slug($participantName, '_') . '_' . Str::slug($testName, '_') . '.pdf';
        $path = 'test_results/' . $fileName;
        Storage::put($path, $pdf->output());
        return $path;
    }
}

