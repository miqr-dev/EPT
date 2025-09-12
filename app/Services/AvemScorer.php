<?php

namespace App\Services;

class AvemScorer
{
    /**
     * Mapping of category codes to their scoring formulas.
     * Each entry contains a constant and an array of question numbers.
     * Positive numbers are added, negative numbers are subtracted.
     */
    protected static array $categories = [
        'SBA' => [
            'label' => 'Subjektive Bedeutsamkeit der Arbeit',
            'constant' => 12,
            'items' => [1, 12, -23, 34, 45, -56],
        ],
        'BE' => [
            'label' => 'Beruflicher Ehrgeiz',
            'constant' => 6,
            'items' => [2, -13, 24, 35, 46, 57],
        ],
        'VB' => [
            'label' => 'Verausgabungsbereitschaft',
            'constant' => 0,
            'items' => [3, 14, 25, 36, 47, 58],
        ],
        'PS' => [
            'label' => 'Perfektionsstreben',
            'constant' => 0,
            'items' => [4, 15, 26, 37, 48, 59],
        ],
        'DF' => [
            'label' => 'Distanzierungsfähigkeit',
            'constant' => 18,
            'items' => [5, -16, 27, 38, -49, -60],
        ],
        'RT' => [
            'label' => 'Resignationstendenz (bei Misserfolg)',
            'constant' => 0,
            'items' => [6, 17, 28, 39, 50, 61],
        ],
        'OP' => [
            'label' => 'Offensive Problembewältigung',
            'constant' => 0,
            'items' => [7, 18, 29, 40, 51, 62],
        ],
        'IR' => [
            'label' => 'Innere Ruhe/Ausgeglichenheit',
            'constant' => 12,
            'items' => [8, -19, -30, 41, 52, 63],
        ],
        'EB' => [
            'label' => 'Erfolgserleben im Beruf',
            'constant' => 6,
            'items' => [9, 20, -31, 42, 53, 64],
        ],
        'LZ' => [
            'label' => 'Lebenszufriedenheit',
            'constant' => 6,
            'items' => [10, 21, 32, 43, -54, 65],
        ],
        'SU' => [
            'label' => 'Erleben sozialer Unterstützung',
            'constant' => 18,
            'items' => [11, -22, -33, 44, -55, 66],
        ],
    ];

    public static function score(array $answers): array
    {
        $answerMap = [];
        foreach ($answers as $entry) {
            $num = $entry['number'] ?? null;
            $ans = $entry['answer'] ?? null;
            if ($num === null || $ans === null) {
                continue;
            }
            $answerMap[(int) $num] = (int) $ans;
        }

        $scores = [];
        foreach (self::$categories as $code => $data) {
            $score = $data['constant'] ?? 0;
            foreach ($data['items'] as $item) {
                $qNum = abs($item);
                $val = $answerMap[$qNum] ?? 0;
                $score += $item < 0 ? -$val : $val;
            }
            $scores[$code] = $score;
        }

        return [
            'category_scores' => $scores,
            'answers' => $answers,
        ];
    }
}

