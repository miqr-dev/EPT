<?php

namespace App\Services;

use App\Models\TestResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MrtPdfService
{
    public static function generate(TestResult $result): ?string
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
            'chart_image' => self::chartImage($json['group_stanines'] ?? []),
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

    protected static function chartImage(array $stanines): ?string
    {
        if (empty($stanines)) {
            return null;
        }
        $config = [
            'type' => 'line',
            'data' => [
                'labels' => ['U1', 'U2', 'U3', 'U4', 'U5', 'U6'],
                'datasets' => [[
                    'label' => 'SN',
                    'data' => array_values($stanines),
                    'borderColor' => '#1d4ed8',
                    'backgroundColor' => '#1d4ed8',
                    'tension' => 0,
                    'pointRadius' => 6,
                    'pointBackgroundColor' => '#1d4ed8',
                    'fill' => false,
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => false],
                    'title' => ['display' => false],
                    'annotation' => [
                        'annotations' => [
                            'rangeBox' => [
                                'type' => 'box',
                                'xMin' => 4,
                                'xMax' => 6,
                                'backgroundColor' => 'rgba(144,238,144,0.3)',
                                'borderWidth' => 0,
                            ],
                        ],
                    ],
                ],
                'indexAxis' => 'y',
                'scales' => [
                    'x' => [
                        'min' => 1,
                        'max' => 9,
                        'ticks' => ['stepSize' => 1],
                    ],
                ],
            ],
            'plugins' => ['chartjs-plugin-annotation'],
        ];
        $url = 'https://quickchart.io/chart?width=480&height=320&c=' . urlencode(json_encode($config));
        try {
            $image = file_get_contents($url);
            if ($image !== false) {
                return base64_encode($image);
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }
}
