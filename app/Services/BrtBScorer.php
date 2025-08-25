<?php

namespace App\Services;

class BrtBScorer
{
  protected static array $questions = [
    ['text' => '819020 – 541600 = ?', 'answers' => ['277420']],
    ['text' => '719020 = 174309 + ?', 'answers' => ['544711']],
    ['text' => '4 : 90 = ?', 'answers' => ['0,044']],
    ['text' => '0,3 · ____ = 0,1', 'answers' => ['0,33', '1/3']],
    ['text' => '1/4 : 1/2 = ?', 'answers' => ['1/2']],
    ['text' => 'Verwandle 0,8 in einen gewöhnlichen Bruch.', 'answers' => ['4/5', '8/10']],
    ['text' => 'Ein Mechaniker hat aus 3 Teilen ein Gerät hergestellt. Die Einzelteile wiegen: 50 g, 9,4 kg, 1050 g. Wie viel wiegt das gesamte Gerät?', 'answers' => ['10500', '10500 g', '10500g', '10,5', '10,5Kg', '10,5kg', '10,5 Kg', '10,5 kg']],
    ['text' => 'Rechne um: Wie viel g sind 9 kg und 1 g?', 'answers' => ['9001', '9001g', '9001 g', '9001 G', '9001G']],
    ['text' => 'Wie viel Zinsen erbringen 2000 € zu 4 % in einem Jahr?', 'answers' => ['80', '80€', '80 €']],
    ['text' => 'Rudi kauft sich ein neues Mofa. Es kostet 1390 €. Bei Barzahlung bekommt er 3 % Rabatt. Wie viel muss er bezahlen?', 'answers' => ['1348,3', '1348,30', '1348,30 €', '1348,3 €', '1348,3€', '1348,30€']],
    ['text' => 'Im Sägewerk können aus einem Baumstamm 20 Bretter von 3 cm Dicke geschnitten werden. Wie viele Bretter erhält man, wenn sie 2 cm dick sind?', 'answers' => ['30']],
    ['text' => 'Im Sägewerk können aus einem Baumstamm 20 Bretter von 3 cm Dicke geschnitten werden. Wie dick wird ein Brett, wenn man 15 Bretter aus dem Stamm schneidet?', 'answers' => ['4', '4cm', '4 cm', '4 Cm', '4Cm']],
    ['text' => 'Berechne die Grundstücksgröße in m².', 'answers' => ['930', '930 m²', '930m²', '930 m2']],
    ['text' => 'Das Rad hat einen Durchmesser von 0,7 m. Welche Strecke legt es zurück, wenn es sich 100 mal dreht?', 'answers' => ['219,8', '219,8 m', '219,8m']],
    ['text' => '√64 = ?', 'answers' => ['8']],
    ['text' => '10² = ?', 'answers' => ['100']],
  ];

  protected static array $rohwertToPR = [
    1 => 0,
    2 => 0,
    3 => 2.5,
    4 => 2.5,
    5 => 5,
    6 => 8.5,
    7 => 16,
    8 => 27,
    9 => 34,
    10 => 53,
    11 => 62,
    12 => 75,
    13 => 85,
    14 => 95,
    15 => 99,
    16 => 100,
  ];

  protected static array $prToTwert = [
    ['pr' => 0, 't' => 30],
    ['pr' => 2, 't' => 30],
    ['pr' => 5, 't' => 34],
    ['pr' => 7, 't' => 35],
    ['pr' => 8, 't' => 36],
    ['pr' => 16, 't' => 40],
    ['pr' => 27, 't' => 44],
    ['pr' => 34, 't' => 46],
    ['pr' => 53, 't' => 50],
    ['pr' => 62, 't' => 53],
    ['pr' => 75, 't' => 56],
    ['pr' => 85, 't' => 60],
    ['pr' => 95, 't' => 66],
    ['pr' => 99, 't' => 73],
    ['pr' => 100, 't' => 80],
  ];

  public static function score(array $answers, array $times): array
  {
    $details = [];
    $correct = 0;
    foreach (self::$questions as $index => $question) {
      $user = $answers[$index] ?? '';
      $isCorrect = self::isCorrectAnswer($user, $question['answers']);
      if ($isCorrect) {
        $correct++;
      }
      $details[] = [
        'question' => $question['text'],
        'user_answer' => $user,
        'time_seconds' => $times[$index] ?? 0,
        'is_correct' => $isCorrect,
      ];
    }

    $pr = self::$rohwertToPR[$correct] ?? 0;
    $twert = self::getTwertFromPR($pr);
    $totalTime = array_sum($times);

    return [
      'rohwert' => $correct,
      'prozentrang' => $pr,
      'twert' => $twert,
      'total_time_seconds' => $totalTime,
      'answers' => $details,
    ];
  }

  public static function addCorrectAnswers(array $result): array
  {
    foreach (self::$questions as $index => $question) {
      if (isset($result['answers'][$index])) {
        $result['answers'][$index]['correct_answers'] = $question['answers'];
      }
    }
    return $result;
  }

  protected static function getTwertFromPR(float $pr): float
  {
    $best = self::$prToTwert[0];
    foreach (self::$prToTwert as $entry) {
      if ($pr >= $entry['pr']) {
        $best = $entry;
      }
    }
    return $best['t'];
  }

  protected static function normalize(string $answer): string
  {
    $normalized = trim(strtolower(str_replace([',', '€', '%', '$', ' '], ['.', '', '', '', ''], $answer)));
    return $normalized;
  }

  protected static function isCorrectAnswer(?string $userAnswer, array $validAnswers): bool
  {
    if ($userAnswer === null) {
      return false;
    }
    $normalizedUser = self::normalize($userAnswer);
    $userNumber = is_numeric($normalizedUser) ? (float)$normalizedUser : null;

    foreach ($validAnswers as $valid) {
      $normalizedValid = self::normalize($valid);
      $validNumber = is_numeric($normalizedValid) ? (float)$normalizedValid : null;
      if ($userNumber !== null && $validNumber !== null) {
        if (abs($userNumber - $validNumber) < 0.001) {
          return true;
        }
      } elseif ($normalizedUser === $normalizedValid) {
        return true;
      }
    }
    return false;
  }
}
