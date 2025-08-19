<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MrtAChartService
{
    /**
     * Correct answers for each of the 60 questions.
     * Indexed from 0.
     */
    protected static array $correctAnswers = [
        ['D'], ['A'], ['C'], ['C'], ['A'], ['B'], ['C'], ['D'], ['C', 'D'], ['D'],
        ['D'], ['C'], ['D'], ['D'], ['B'], ['A'], ['A'], ['D'], ['B'], ['B'],
        ['A'], ['A'], ['C'], ['B'], ['C'], ['D'], ['D'], ['D'], ['B'], ['C'],
        ['C'], ['B'], ['A'], ['B'], ['D'], ['B'], ['A'], ['B'], ['D'], ['D'],
        ['B'], ['A'], ['A'], ['C'], ['B'], ['A'], ['B'], ['C'], ['A'], ['D'],
        ['D'], ['B'], ['D'], ['A', 'C'], ['A', 'C'], ['B'], ['D'], ['C'], ['D'], ['D'],
    ];

    /**
     * Mapping of question indices to groups U1–U6.
     */
    protected static array $groupMap = [
        [0, 1, 2, 3, 4, 15, 16, 17, 18, 19],
        [5, 6, 7, 8, 9, 20, 21, 22, 23, 24],
        [10, 11, 12, 13, 14, 25, 26, 27, 28, 29],
        [30, 31, 32, 33, 34, 45, 46, 47, 48, 49],
        [35, 36, 37, 38, 39, 50, 51, 52, 53, 54],
        [40, 41, 42, 43, 44, 55, 56, 57, 58, 59],
    ];

    protected static array $snMatrix18_30 = [
        [1,1,1,1,2,3,3,4,5,6,9],
        [1,1,1,2,3,4,5,6,7,8,9],
        [1,1,1,1,2,2,3,4,5,7,9],
        [1,1,1,1,1,1,2,3,4,6,9],
        [1,1,2,3,4,4,5,6,7,9,9],
        [1,1,1,1,2,3,3,4,5,7,9],
    ];

    protected static array $snMatrix31_50 = [
        [1,1,1,2,2,3,4,5,5,7,9],
        [1,1,1,1,2,3,4,4,5,6,9],
        [1,1,1,2,2,3,4,5,6,7,9],
        [1,1,1,1,1,2,2,3,4,6,9],
        [1,2,2,4,4,5,6,7,8,9,9],
        [1,1,1,2,3,3,4,5,6,7,9],
    ];

    /**
     * Compute group scores and stanines from user answers and age.
     */
    public static function compute(array $answers, ?int $age): array
    {
        $groupScores = [];
        foreach (self::$groupMap as $indices) {
            $score = 0;
            foreach ($indices as $idx) {
                $ans = $answers[$idx] ?? null;
                if (self::isCorrect($ans, self::$correctAnswers[$idx] ?? [])) {
                    $score++;
                }
            }
            $groupScores[] = $score;
        }

        $matrix = ($age !== null && $age >= 31)
            ? self::$snMatrix31_50
            : self::$snMatrix18_30;

        $groupStanines = [];
        foreach ($groupScores as $groupIdx => $rw) {
            $safe = max(0, min(10, $rw));
            $groupStanines[] = $matrix[$groupIdx][$safe];
        }

        return [
            'group_scores'   => $groupScores,
            'group_stanines' => $groupStanines,
        ];
    }

    /**
     * Generate chart PNG in base64 using QuickChart.
     */
    public static function chartBase64(array $stanines): ?string
    {
        $config = [
            'type' => 'line',
            'data' => [
                'labels' => ['U1','U2','U3','U4','U5','U6'],
                'datasets' => [[
                    'label' => 'SN',
                    'data' => $stanines,
                    'borderColor' => '#1d4ed8',
                    'backgroundColor' => '#1d4ed8',
                    'tension' => 0,
                    'pointRadius' => 6,
                    'pointBackgroundColor' => '#1d4ed8',
                    'fill' => false,
                ]],
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => ['display' => false],
                    'title'  => ['display' => false],
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
        ];

        $response = Http::timeout(15)->asForm()->post('https://quickchart.io/chart', [
            'chart' => json_encode($config),
            'encoding' => 'base64',
            'format' => 'png',
        ]);

        return $response->successful() ? $response->body() : null;
    }

    /**
     * Compute scores and generate chart.
     */
    public static function generate(array $answers, ?int $age): array
    {
        $data = self::compute($answers, $age);
        $data['chart_base64'] = self::chartBase64($data['group_stanines']);
        return $data;
    }

    protected static function isCorrect(?string $answer, array $valid): bool
    {
        if ($answer === null) {
            return false;
        }
        $answer = strtoupper($answer);
        foreach ($valid as $v) {
            if ($answer === strtoupper($v)) {
                return true;
            }
        }
        return false;
    }
}
