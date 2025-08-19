<?php

namespace App\Services;

class MrtAScorer
{
    protected static array $questions = [
        ['number' => 1, 'correct' => ['D']],
        ['number' => 2, 'correct' => ['A']],
        ['number' => 3, 'correct' => ['C']],
        ['number' => 4, 'correct' => ['C']],
        ['number' => 5, 'correct' => ['A']],
        ['number' => 6, 'correct' => ['B']],
        ['number' => 7, 'correct' => ['C']],
        ['number' => 8, 'correct' => ['D']],
        ['number' => 9, 'correct' => ['C', 'D']],
        ['number' => 10, 'correct' => ['D']],
        ['number' => 11, 'correct' => ['D']],
        ['number' => 12, 'correct' => ['C']],
        ['number' => 13, 'correct' => ['D']],
        ['number' => 14, 'correct' => ['D']],
        ['number' => 15, 'correct' => ['B']],
        ['number' => 16, 'correct' => ['A']],
        ['number' => 17, 'correct' => ['A']],
        ['number' => 18, 'correct' => ['D']],
        ['number' => 19, 'correct' => ['B']],
        ['number' => 20, 'correct' => ['B']],
        ['number' => 21, 'correct' => ['A']],
        ['number' => 22, 'correct' => ['A']],
        ['number' => 23, 'correct' => ['C']],
        ['number' => 24, 'correct' => ['B']],
        ['number' => 25, 'correct' => ['C']],
        ['number' => 26, 'correct' => ['D']],
        ['number' => 27, 'correct' => ['D']],
        ['number' => 28, 'correct' => ['D']],
        ['number' => 29, 'correct' => ['B']],
        ['number' => 30, 'correct' => ['C']],
        ['number' => 31, 'correct' => ['C']],
        ['number' => 32, 'correct' => ['B']],
        ['number' => 33, 'correct' => ['A']],
        ['number' => 34, 'correct' => ['B']],
        ['number' => 35, 'correct' => ['D']],
        ['number' => 36, 'correct' => ['B']],
        ['number' => 37, 'correct' => ['A']],
        ['number' => 38, 'correct' => ['B']],
        ['number' => 39, 'correct' => ['D']],
        ['number' => 40, 'correct' => ['D']],
        ['number' => 41, 'correct' => ['B']],
        ['number' => 42, 'correct' => ['A']],
        ['number' => 43, 'correct' => ['A']],
        ['number' => 44, 'correct' => ['C']],
        ['number' => 45, 'correct' => ['B']],
        ['number' => 46, 'correct' => ['A']],
        ['number' => 47, 'correct' => ['B']],
        ['number' => 48, 'correct' => ['C']],
        ['number' => 49, 'correct' => ['A']],
        ['number' => 50, 'correct' => ['D']],
        ['number' => 51, 'correct' => ['D']],
        ['number' => 52, 'correct' => ['B']],
        ['number' => 53, 'correct' => ['D']],
        ['number' => 54, 'correct' => ['A', 'C']],
        ['number' => 55, 'correct' => ['A', 'C']],
        ['number' => 56, 'correct' => ['B']],
        ['number' => 57, 'correct' => ['D']],
        ['number' => 58, 'correct' => ['C']],
        ['number' => 59, 'correct' => ['D']],
        ['number' => 60, 'correct' => ['D']],
    ];

    protected static array $groupMap = [
        [0,1,2,3,4,15,16,17,18,19],
        [5,6,7,8,9,20,21,22,23,24],
        [10,11,12,13,14,25,26,27,28,29],
        [30,31,32,33,34,45,46,47,48,49],
        [35,36,37,38,39,50,51,52,53,54],
        [40,41,42,43,44,55,56,57,58,59],
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

    protected static array $prTable18_30 = [
        ['rwgs'=>9,'pr'=>0], ['rwgs'=>10,'pr'=>0], ['rwgs'=>11,'pr'=>0], ['rwgs'=>12,'pr'=>0], ['rwgs'=>13,'pr'=>0], ['rwgs'=>14,'pr'=>0],
        ['rwgs'=>15,'pr'=>0], ['rwgs'=>16,'pr'=>0], ['rwgs'=>17,'pr'=>0], ['rwgs'=>18,'pr'=>1], ['rwgs'=>19,'pr'=>1], ['rwgs'=>20,'pr'=>1],
        ['rwgs'=>21,'pr'=>2], ['rwgs'=>22,'pr'=>2], ['rwgs'=>23,'pr'=>3], ['rwgs'=>24,'pr'=>3], ['rwgs'=>25,'pr'=>3], ['rwgs'=>26,'pr'=>3],
        ['rwgs'=>27,'pr'=>3], ['rwgs'=>28,'pr'=>4], ['rwgs'=>29,'pr'=>5], ['rwgs'=>30,'pr'=>7], ['rwgs'=>31,'pr'=>8], ['rwgs'=>32,'pr'=>10],
        ['rwgs'=>33,'pr'=>12], ['rwgs'=>34,'pr'=>13], ['rwgs'=>35,'pr'=>16], ['rwgs'=>36,'pr'=>18], ['rwgs'=>37,'pr'=>18], ['rwgs'=>38,'pr'=>21],
        ['rwgs'=>39,'pr'=>24], ['rwgs'=>40,'pr'=>27], ['rwgs'=>41,'pr'=>31], ['rwgs'=>42,'pr'=>34], ['rwgs'=>43,'pr'=>38], ['rwgs'=>44,'pr'=>42],
        ['rwgs'=>45,'pr'=>46], ['rwgs'=>46,'pr'=>50], ['rwgs'=>47,'pr'=>54], ['rwgs'=>48,'pr'=>58], ['rwgs'=>49,'pr'=>62], ['rwgs'=>50,'pr'=>69],
        ['rwgs'=>51,'pr'=>73], ['rwgs'=>52,'pr'=>79], ['rwgs'=>53,'pr'=>84], ['rwgs'=>54,'pr'=>88], ['rwgs'=>55,'pr'=>93], ['rwgs'=>56,'pr'=>96],
        ['rwgs'=>57,'pr'=>98], ['rwgs'=>58,'pr'=>99], ['rwgs'=>59,'pr'=>100], ['rwgs'=>60,'pr'=>100],
    ];

    protected static array $prTable31_50 = [
        ['rwgs'=>9,'pr'=>0], ['rwgs'=>10,'pr'=>0], ['rwgs'=>11,'pr'=>0], ['rwgs'=>12,'pr'=>0], ['rwgs'=>13,'pr'=>0], ['rwgs'=>14,'pr'=>0],
        ['rwgs'=>15,'pr'=>1], ['rwgs'=>16,'pr'=>1], ['rwgs'=>17,'pr'=>1], ['rwgs'=>18,'pr'=>1], ['rwgs'=>19,'pr'=>3], ['rwgs'=>20,'pr'=>3],
        ['rwgs'=>21,'pr'=>3], ['rwgs'=>22,'pr'=>3], ['rwgs'=>23,'pr'=>4], ['rwgs'=>24,'pr'=>5], ['rwgs'=>25,'pr'=>5], ['rwgs'=>26,'pr'=>7],
        ['rwgs'=>27,'pr'=>8], ['rwgs'=>28,'pr'=>10], ['rwgs'=>29,'pr'=>10], ['rwgs'=>30,'pr'=>12], ['rwgs'=>31,'pr'=>13], ['rwgs'=>32,'pr'=>13],
        ['rwgs'=>33,'pr'=>16], ['rwgs'=>34,'pr'=>18], ['rwgs'=>35,'pr'=>21], ['rwgs'=>36,'pr'=>21], ['rwgs'=>37,'pr'=>24], ['rwgs'=>38,'pr'=>27],
        ['rwgs'=>39,'pr'=>31], ['rwgs'=>40,'pr'=>34], ['rwgs'=>41,'pr'=>38], ['rwgs'=>42,'pr'=>42], ['rwgs'=>43,'pr'=>42], ['rwgs'=>44,'pr'=>46],
        ['rwgs'=>45,'pr'=>50], ['rwgs'=>46,'pr'=>58], ['rwgs'=>47,'pr'=>62], ['rwgs'=>48,'pr'=>62], ['rwgs'=>49,'pr'=>66], ['rwgs'=>50,'pr'=>79],
        ['rwgs'=>51,'pr'=>79], ['rwgs'=>52,'pr'=>82], ['rwgs'=>53,'pr'=>84], ['rwgs'=>54,'pr'=>86], ['rwgs'=>55,'pr'=>90], ['rwgs'=>56,'pr'=>95],
        ['rwgs'=>57,'pr'=>98], ['rwgs'=>58,'pr'=>99], ['rwgs'=>59,'pr'=>100], ['rwgs'=>60,'pr'=>100],
    ];

    public static function score(array $answers, array $times, ?int $age): array
    {
        $details = [];
        $correctFlags = [];
        foreach (self::$questions as $index => $q) {
            $user = $answers[$index] ?? null;
            $isCorrect = self::isCorrect($user, $q['correct']);
            $correctFlags[$index] = $isCorrect ? 1 : 0;
            $details[] = [
                'number' => $q['number'],
                'user_answer' => $user,
                'correct_answers' => $q['correct'],
                'time_seconds' => $times[$index] ?? 0,
                'is_correct' => $isCorrect,
            ];
        }

        $groupScores = [];
        foreach (self::$groupMap as $group) {
            $sum = 0;
            foreach ($group as $idx) {
                $sum += $correctFlags[$idx] ?? 0;
            }
            $groupScores[] = $sum;
        }

        $matrix = ($age !== null && $age >= 31) ? self::$snMatrix31_50 : self::$snMatrix18_30;
        $groupStanines = [];
        foreach ($groupScores as $gIdx => $score) {
            $safe = max(0, min(10, $score));
            $groupStanines[] = $matrix[$gIdx][$safe] ?? null;
        }

        $totalScore = array_sum($groupScores);
        $prTable = ($age !== null && $age >= 31) ? self::$prTable31_50 : self::$prTable18_30;
        $pr = 0;
        foreach ($prTable as $row) {
            if ($row['rwgs'] === $totalScore) {
                $pr = $row['pr'];
                break;
            }
        }

        $totalTime = array_sum($times);

        return [
            'group_scores' => $groupScores,
            'group_stanines' => $groupStanines,
            'total_score' => $totalScore,
            'prozentrang' => $pr,
            'total_time_seconds' => $totalTime,
            'answers' => $details,
        ];
    }

    protected static function isCorrect(?string $user, array $valid): bool
    {
        if (!$user) {
            return false;
        }
        $u = strtoupper(trim($user));
        foreach ($valid as $v) {
            if ($u === strtoupper(trim($v))) {
                return true;
            }
        }
        return false;
    }
}
