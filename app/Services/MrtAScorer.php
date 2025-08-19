<?php

namespace App\Services;

class MrtAScorer
{
    protected static array $mrtQuestions = [
        ['number' => 1, 'options' => ["Drehohrgel", "Dreohrgel", "Dreorgel", "Drehorgel"], 'correct' => ["D"]],
        ['number' => 2, 'options' => ["Spülmaschine", "Spühlmaschiene", "Spülmaschiene", "Spühlmaschine"], 'correct' => ["A"]],
        ['number' => 3, 'options' => ["unwiederstelich", "unwiederstehlich", "unwiderstehlich", "unwiderstelich"], 'correct' => ["C"]],
        ['number' => 4, 'options' => ["Heerschahr", "Herschar", "Heerschar", "Heerschaar"], 'correct' => ["C"]],
        ['number' => 5, 'options' => ["ehrerbietig", "ererbietig", "ehrerbitig", "ehrehrbietig"], 'correct' => ["A"]],
        ['number' => 6, 'options' => ["Musikapelle", "Musikkapelle", "Musikkappelle", "Musikappelle"], 'correct' => ["B"]],
        ['number' => 7, 'options' => ["Karrussell", "Karrussel", "Karussell", "Karusell"], 'correct' => ["C"]],
        ['number' => 8, 'options' => ["Apettit", "Appetitt", "Appettit", "Appetit"], 'correct' => ["D"]],
        ['number' => 9, 'options' => ["Brennesel", "Brennnesel", "Brennessel", "Brennnessel"], 'correct' => ["C", "D"]],
        ['number' => 10, 'options' => ["Kontrollaparrat", "Kontrollapparrat", "Kontrolapparat", "Kontrollapparat"], 'correct' => ["D"]],
        ['number' => 11, 'options' => ["Gutmüdigkeit", "Gutmüdichkeit", "Gutmütigkeid", "Gutmütigkeit"], 'correct' => ["D"]],
        ['number' => 12, 'options' => ["täglisch und vierzehntägig", "täglich und vierzehntägich", "täglich und vierzehntägig", "täglig und vierzehntägig"], 'correct' => ["C"]],
        ['number' => 13, 'options' => ["Flukzeugwerk", "Flugzeukwerk", "Flugzeugwerg", "Flugzeugwerk"], 'correct' => ["D"]],
        ['number' => 14, 'options' => ["unendgeldlich", "unendgeltlich", "unentgeldlich", "unentgeltlich"], 'correct' => ["D"]],
        ['number' => 15, 'options' => ["am bedeutendsden", "am bedeutendsten", "am bedeutentsten", "am bedeudendsten"], 'correct' => ["B"]],
        ['number' => 16, 'options' => ["Liberalisierung", "Lieberalisirung", "Lieberalisierung", "Liberalisirung"], 'correct' => ["A"]],
        ['number' => 17, 'options' => ["zwei menschenleere Säle", "zwei menschenlehre Säle", "zwei menschenlehre Sääle", "zwei menschenleere Sääle"], 'correct' => ["A"]],
        ['number' => 18, 'options' => ["Nämaschiene", "Nähmaschiene", "Nämaschine", "Nähmaschine"], 'correct' => ["D"]],
        ['number' => 19, 'options' => ["wiederstandsfäig", "widerstandsfähig", "widerstandsfäig", "wiederstandsfähig"], 'correct' => ["B"]],
        ['number' => 20, 'options' => ["Wahreneinfuhr", "Wareneinfuhr", "Wahreneinfur", "Wareneinfur"], 'correct' => ["B"]],
        ['number' => 21, 'options' => ["Kanallotse", "Kannallotse", "Kanallotze", "Kannalotse"], 'correct' => ["A"]],
        ['number' => 22, 'options' => ["aggressiv", "agressiv", "agresiv", "aggresiv"], 'correct' => ["A"]],
        ['number' => 23, 'options' => ["Komission", "Komision", "Kommission", "Kommision"], 'correct' => ["C"]],
        ['number' => 24, 'options' => ["Dillemma", "Dilemma", "Dilema", "Dillema"], 'correct' => ["B"]],
        ['number' => 25, 'options' => ["Rasieraparrat", "Rassierapparat", "Rasierapparat", "Rasieraparat"], 'correct' => ["C"]],
        ['number' => 26, 'options' => ["Revormforschlag", "Reformforschlag", "Revormvorschlag", "Reformvorschlag"], 'correct' => ["D"]],
        ['number' => 27, 'options' => ["tugenhaft", "tugenhafd", "tugenthaft", "tugendhaft"], 'correct' => ["D"]],
        ['number' => 28, 'options' => ["Angeglagter", "Angeklakter", "Angeglakter", "Angeklagter"], 'correct' => ["D"]],
        ['number' => 29, 'options' => ["Gewandheit", "Gewandtheit", "Gewandtheid", "Gewantheit"], 'correct' => ["B"]],
        ['number' => 30, 'options' => ["schaudernt", "schauternt", "schaudernd", "schauternd"], 'correct' => ["C"]],
        ['number' => 31, 'options' => ["Bundespräsidänt", "Bundespresident", "Bundespräsident", "Bundespresidänt"], 'correct' => ["C"]],
        ['number' => 32, 'options' => ["Sekrätärin", "Sekretärin", "Säkretärin", "Sekräterin"], 'correct' => ["B"]],
        ['number' => 33, 'options' => ["repräsentativ", "representativ", "repräsäntativ", "räpräsentativ"], 'correct' => ["A"]],
        ['number' => 34, 'options' => ["annähärnd", "annähernd", "annehärnd", "annehernd"], 'correct' => ["B"]],
        ['number' => 35, 'options' => ["ärgärlich", "ergerlich", "ergärlich", "ärgerlich"], 'correct' => ["D"]],
        ['number' => 36, 'options' => ["vom kleinen auf das Große schließen", "vom Kleinen auf das Große schließen", "vom Kleinen auf das große schließen", "vom kleinen auf das große schließen"], 'correct' => ["B"]],
        ['number' => 37, 'options' => ["im Dunkel der Nacht", "im dunkel der nacht", "im Dunkel der nacht", "im dunkel der Nacht"], 'correct' => ["A"]],
        ['number' => 38, 'options' => ["sie hat angst und mir ist auch Angst", "sie hat Angst und mir ist auch angst", "sie hat Angst und mir ist auch Angst", "sie hat angst und mir ist auch angst"], 'correct' => ["B"]],
        ['number' => 39, 'options' => ["wir froren ganze nächtelang vor Kälte", "wir froren ganze Nächtelang vor Kälte", "wir froren ganze nächte lang vor Kälte", "wir froren ganze Nächte lang vor Kälte"], 'correct' => ["D"]],
        ['number' => 40, 'options' => ["ein einzelnes paar Schuhe", "ein Einzelnes paar Schuhe", "ein Einzelnes Paar Schuhe", "ein einzelnes Paar Schuhe"], 'correct' => ["D"]],
        ['number' => 41, 'options' => ["Insditution", "Institution", "Instidution", "Insdidution"], 'correct' => ["B"]],
        ['number' => 42, 'options' => ["generell", "gennerrell", "gennerell", "generrell"], 'correct' => ["A"]],
        ['number' => 43, 'options' => ["Identifizierung", "Idäntifizierung", "Identifizirung", "Itentifizierung"], 'correct' => ["A"]],
        ['number' => 44, 'options' => ["Differrenz", "Diferrenz", "Differenz", "Diferenz"], 'correct' => ["C"]],
        ['number' => 45, 'options' => ["Sattellit", "Satellit", "Satellid", "Satelit"], 'correct' => ["B"]],
        ['number' => 46, 'options' => ["die Maid ist zartbesaitet", "die Maid ist zartbeseitet", "die Meid ist zartbesaitet", "die Meid ist zartbeseitet"], 'correct' => ["A"]],
        ['number' => 47, 'options' => ["Laubsegeblätter", "Laubsägeblätter", "Laubsegäblätter", "Laubsägebletter"], 'correct' => ["B"]],
        ['number' => 48, 'options' => ["Dräschflegel", "Dräschflägel", "Dreschflegel", "Dreschflägel"], 'correct' => ["C"]],
        ['number' => 49, 'options' => ["unabänderlich", "unabendärlich", "unabändärlich", "unabenderlich"], 'correct' => ["A"]],
        ['number' => 50, 'options' => ["Salzbräzel", "Salzbräzäl", "Salzbrezäl", "Salzbrezel"], 'correct' => ["D"]],
        ['number' => 51, 'options' => ["das für und wider", "das für und Wider", "das Für und wider", "das Für und Wider"], 'correct' => ["D"]],
        ['number' => 52, 'options' => ["über kurz oder Lang", "über kurz oder lang", "über Kurz oder Lang", "über Kurz oder lang"], 'correct' => ["B"]],
        ['number' => 53, 'options' => ["es sangen junge und alte, klein und groß", "es sangen Junge und Alte, Klein und Groß", "es sangen junge und alte, Klein und Groß", "es sangen Junge und Alte, klein und groß"], 'correct' => ["D"]],
        ['number' => 54, 'options' => ["er kam gestern mittag", "er kam Gestern Mittag", "er kam gestern Mittag", "er kam Gestern mittag"], 'correct' => ["A", "C"]],
        ['number' => 55, 'options' => ["sie kann gut maschineschreiben", "sie kann gut Maschineschreiben", "sie kann gut Maschine schreiben", "sie kann gut maschine schreiben"], 'correct' => ["A", "C"]],
        ['number' => 56, 'options' => ["Allmosen", "Almosen", "Almoosen", "Allmoosen"], 'correct' => ["B"]],
        ['number' => 57, 'options' => ["debbattieren", "debattiren", "debbatieren", "debattieren"], 'correct' => ["D"]],
        ['number' => 58, 'options' => ["Witalität", "Vitalidät", "Vitalität", "Vitallität"], 'correct' => ["C"]],
        ['number' => 59, 'options' => ["Arbeitsmillieu", "Arbeitsmiliö", "Arbeitsmülieu", "Arbeitsmilieu"], 'correct' => ["D"]],
        ['number' => 60, 'options' => ["Interwiew", "Interviev", "Interwiu", "Interview"], 'correct' => ["D"]],
    ];

    protected static array $groupMap = [
        [0, 1, 2, 3, 4, 15, 16, 17, 18, 19],
        [5, 6, 7, 8, 9, 20, 21, 22, 23, 24],
        [10, 11, 12, 13, 14, 25, 26, 27, 28, 29],
        [30, 31, 32, 33, 34, 45, 46, 47, 48, 49],
        [35, 36, 37, 38, 39, 50, 51, 52, 53, 54],
        [40, 41, 42, 43, 44, 55, 56, 57, 58, 59],
    ];

    protected static array $SN_WERTE_MATRIX_18_30 = [
        [1, 1, 1, 1, 2, 3, 3, 4, 5, 6, 9],
        [1, 1, 1, 2, 3, 4, 5, 6, 7, 8, 9],
        [1, 1, 1, 1, 2, 2, 3, 4, 5, 7, 9],
        [1, 1, 1, 1, 1, 1, 2, 3, 4, 6, 9],
        [1, 1, 2, 3, 4, 4, 5, 6, 7, 9, 9],
        [1, 1, 1, 1, 2, 3, 3, 4, 5, 7, 9],
    ];

    protected static array $SN_WERTE_MATRIX_31_50 = [
        [1, 1, 1, 2, 2, 3, 4, 5, 5, 7, 9],
        [1, 1, 1, 1, 2, 3, 4, 4, 5, 6, 9],
        [1, 1, 1, 2, 2, 3, 4, 5, 6, 7, 9],
        [1, 1, 1, 1, 1, 2, 2, 3, 4, 6, 9],
        [1, 2, 2, 4, 4, 5, 6, 7, 8, 9, 9],
        [1, 1, 1, 2, 3, 3, 4, 5, 6, 7, 9],
    ];

    protected static array $prTable_18_30 = [['rwgs' => 9, 'PR' => 0], ['rwgs' => 10, 'PR' => 0], ['rwgs' => 11, 'PR' => 0], ['rwgs' => 12, 'PR' => 0], ['rwgs' => 13, 'PR' => 0], ['rwgs' => 14, 'PR' => 0], ['rwgs' => 15, 'PR' => 0], ['rwgs' => 16, 'PR' => 0], ['rwgs' => 17, 'PR' => 0], ['rwgs' => 18, 'PR' => 1], ['rwgs' => 19, 'PR' => 1], ['rwgs' => 20, 'PR' => 1], ['rwgs' => 21, 'PR' => 2], ['rwgs' => 22, 'PR' => 2], ['rwgs' => 23, 'PR' => 3], ['rwgs' => 24, 'PR' => 3], ['rwgs' => 25, 'PR' => 3], ['rwgs' => 26, 'PR' => 3], ['rwgs' => 27, 'PR' => 3], ['rwgs' => 28, 'PR' => 4], ['rwgs' => 29, 'PR' => 5], ['rwgs' => 30, 'PR' => 7], ['rwgs' => 31, 'PR' => 8], ['rwgs' => 32, 'PR' => 10], ['rwgs' => 33, 'PR' => 12], ['rwgs' => 34, 'PR' => 13], ['rwgs' => 35, 'PR' => 16], ['rwgs' => 36, 'PR' => 18], ['rwgs' => 37, 'PR' => 18], ['rwgs' => 38, 'PR' => 21], ['rwgs' => 39, 'PR' => 24], ['rwgs' => 40, 'PR' => 27], ['rwgs' => 41, 'PR' => 31], ['rwgs' => 42, 'PR' => 34], ['rwgs' => 43, 'PR' => 38], ['rwgs' => 44, 'PR' => 42], ['rwgs' => 45, 'PR' => 46], ['rwgs' => 46, 'PR' => 50], ['rwgs' => 47, 'PR' => 54], ['rwgs' => 48, 'PR' => 58], ['rwgs' => 49, 'PR' => 62], ['rwgs' => 50, 'PR' => 69], ['rwgs' => 51, 'PR' => 73], ['rwgs' => 52, 'PR' => 79], ['rwgs' => 53, 'PR' => 84], ['rwgs' => 54, 'PR' => 88], ['rwgs' => 55, 'PR' => 93], ['rwgs' => 56, 'PR' => 96], ['rwgs' => 57, 'PR' => 98], ['rwgs' => 58, 'PR' => 99], ['rwgs' => 59, 'PR' => 100], ['rwgs' => 60, 'PR' => 100]];
    protected static array $prTable_31_50 = [['rwgs' => 9, 'PR' => 0], ['rwgs' => 10, 'PR' => 0], ['rwgs' => 11, 'PR' => 0], ['rwgs' => 12, 'PR' => 0], ['rwgs' => 13, 'PR' => 0], ['rwgs' => 14, 'PR' => 0], ['rwgs' => 15, 'PR' => 1], ['rwgs' => 16, 'PR' => 1], ['rwgs' => 17, 'PR' => 1], ['rwgs' => 18, 'PR' => 1], ['rwgs' => 19, 'PR' => 3], ['rwgs' => 20, 'PR' => 3], ['rwgs' => 21, 'PR' => 3], ['rwgs' => 22, 'PR' => 3], ['rwgs' => 23, 'PR' => 4], ['rwgs' => 24, 'PR' => 5], ['rwgs' => 25, 'PR' => 5], ['rwgs' => 26, 'PR' => 7], ['rwgs' => 27, 'PR' => 8], ['rwgs' => 28, 'PR' => 10], ['rwgs' => 29, 'PR' => 10], ['rwgs' => 30, 'PR' => 12], ['rwgs' => 31, 'PR' => 13], ['rwgs' => 32, 'PR' => 13], ['rwgs' => 33, 'PR' => 16], ['rwgs' => 34, 'PR' => 18], ['rwgs' => 35, 'PR' => 21], ['rwgs' => 36, 'PR' => 21], ['rwgs' => 37, 'PR' => 24], ['rwgs' => 38, 'PR' => 27], ['rwgs' => 39, 'PR' => 31], ['rwgs' => 40, 'PR' => 34], ['rwgs' => 41, 'PR' => 38], ['rwgs' => 42, 'PR' => 42], ['rwgs' => 43, 'PR' => 42], ['rwgs' => 44, 'PR' => 46], ['rwgs' => 45, 'PR' => 50], ['rwgs' => 46, 'PR' => 58], ['rwgs' => 47, 'PR' => 62], ['rwgs' => 48, 'PR' => 62], ['rwgs' => 49, 'PR' => 66], ['rwgs' => 50, 'PR' => 79], ['rwgs' => 51, 'PR' => 79], ['rwgs' => 52, 'PR' => 82], ['rwgs' => 53, 'PR' => 84], ['rwgs' => 54, 'PR' => 86], ['rwgs' => 55, 'PR' => 90], ['rwgs' => 56, 'PR' => 95], ['rwgs' => 57, 'PR' => 98], ['rwgs' => 58, 'PR' => 99], ['rwgs' => 59, 'PR' => 100], ['rwgs' => 60, 'PR' => 100]];

    public static function score(array $userAnswers, ?int $userAge, array $questionTimes): array
    {
        $groupScores = self::calculateGroupScores($userAnswers);
        $totalScore = array_sum($groupScores);

        $selectedMatrix = ($userAge >= 31) ? self::$SN_WERTE_MATRIX_31_50 : self::$SN_WERTE_MATRIX_18_30;
        $groupStanines = self::calculateGroupStanines($groupScores, $selectedMatrix);

        $selectedPRTable = ($userAge >= 31) ? self::$prTable_31_50 : self::$prTable_18_30;
        $prValue = self::getPR($totalScore, $selectedPRTable);

        $totalTimeTaken = array_sum($questionTimes);

        $details = [];
        foreach (self::$mrtQuestions as $index => $question) {
            $details[] = [
                'number' => $question['number'],
                'user_answer' => $userAnswers[$index] ?? null,
                'correct_answers' => $question['correct'],
                'time_seconds' => $questionTimes[$index] ?? 0,
                'is_correct' => self::isCorrectAnswer($userAnswers[$index] ?? null, $question['correct']),
            ];
        }

        return [
            'group_scores' => $groupScores,
            'group_stanines' => $groupStanines,
            'total_score' => $totalScore,
            'prozentrang' => $prValue,
            'total_time_seconds' => $totalTimeTaken,
            'answers' => $details,
        ];
    }

    private static function calculateGroupScores(array $userAnswers): array
    {
        $groupScores = [];
        foreach (self::$groupMap as $indices) {
            $score = 0;
            foreach ($indices as $idx) {
                $ans = $userAnswers[$idx] ?? null;
                if ($ans && self::isCorrectAnswer($ans, self::$mrtQuestions[$idx]['correct'])) {
                    $score++;
                }
            }
            $groupScores[] = $score;
        }
        return $groupScores;
    }

    private static function calculateGroupStanines(array $groupScores, array $matrix): array
    {
        $stanines = [];
        foreach ($groupScores as $groupIdx => $rw) {
            $safeRW = max(0, min(10, $rw));
            $stanines[] = $matrix[$groupIdx][$safeRW];
        }
        return $stanines;
    }

    private static function getPR(int $rwgs, array $prTable): int
    {
        foreach ($prTable as $row) {
            if ($row['rwgs'] === $rwgs) {
                return $row['PR'];
            }
        }
        return 0;
    }

    private static function isCorrectAnswer(?string $userAnswer, array $validAnswers): bool
    {
        if (!$userAnswer) {
            return false;
        }
        foreach ($validAnswers as $valid) {
            if (strtoupper($userAnswer) === strtoupper($valid)) {
                return true;
            }
        }
        return false;
    }
}
