<?php

namespace App\Services;

class FpiRScorer
{
    protected static ?array $questions = null;
    protected static array $categoryKeys = [1,2,3,4,5,6,7,8,9,10,'E','N'];
    protected static array $stanineKeys = ['LEB','SOZ','LEI','GEH','ERR','AGGR','BEAN','KORP','GES','OFF','EXTR','EMOT'];

    protected static function loadQuestions(): array
    {
        if (self::$questions === null) {
            $path = __DIR__ . '/FpiR/questions.json';
            self::$questions = json_decode(file_get_contents($path), true);
        }
        return self::$questions;
    }

    protected static function getNormTable(?string $sex, ?int $age): ?array
    {
        if (!$sex || !$age) {
            return null;
        }
        $sex = strtolower($sex) === 'f' ? 'female' : 'male';
        if ($age >= 16 && $age <= 24) {
            $range = '16_24';
        } elseif ($age >= 25 && $age <= 44) {
            $range = '25_44';
        } elseif ($age >= 45 && $age <= 59) {
            $range = '45_59';
        } else {
            $range = '60up';
        }
        $file = __DIR__ . "/FpiR/norms_{$sex}_{$range}.json";
        if (!file_exists($file)) {
            return null;
        }
        return json_decode(file_get_contents($file), true);
    }

    public static function score(array $answers, ?string $sex, ?int $age, ?int $totalTimeSeconds = null): array
    {
        $questions = self::loadQuestions();
        $questionMap = [];
        foreach ($questions as $q) {
            $questionMap[$q['number']] = $q;
        }

        $scores = [];
        foreach (self::$categoryKeys as $key) {
            $scores[$key] = 0;
        }

        $details = [];
        foreach ($answers as $entry) {
            $num = $entry['number'] ?? null;
            $ans = $entry['answer'] ?? null;
            if ($num === null || $ans === null) {
                continue;
            }
            $q = $questionMap[$num] ?? null;
            $text = $q['text'] ?? '';
            $details[] = [
                'number' => $num,
                'text' => $text,
                'answer' => $ans,
            ];
            if ($q && isset($q[$ans])) {
                foreach ($q[$ans] as $effect) {
                    $cat = $effect['category'];
                    $points = $effect['points'];
                    $scores[$cat] = ($scores[$cat] ?? 0) + $points;
                }
            }
        }

        $stanines = [];
        $normTable = self::getNormTable($sex, $age);
        if ($normTable) {
            foreach (self::$stanineKeys as $index => $key) {
                $scoreKey = self::$categoryKeys[$index];
                $value = $scores[$scoreKey] ?? 0;
                $stanines[$key] = null;
                if (isset($normTable[$key])) {
                    foreach ($normTable[$key] as $i => $range) {
                        [$min, $max] = $range;
                        if ($value >= $min && $value <= $max) {
                            $stanines[$key] = $i + 1;
                            break;
                        }
                    }
                }
            }
        }

        $rohwerte = [];
        foreach (self::$categoryKeys as $key) {
            $rohwerte[] = $scores[$key];
        }
        $stanineArray = [];
        foreach (self::$stanineKeys as $key) {
            $stanineArray[] = $stanines[$key] ?? null;
        }

        return [
            'category_scores' => $scores,
            'category_stanines' => $stanines,
            'rohwerte' => $rohwerte,
            'stanines' => $stanineArray,
            'total_time_seconds' => $totalTimeSeconds,
            'answers' => $details,
        ];
    }
}

