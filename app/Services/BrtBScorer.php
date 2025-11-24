<?php

namespace App\Services;

class BrtBScorer
{
  protected static array $questions = [
    ['text' => '619020 – 540600 = ?', 'answers' => ['78420']],
    ['text' => '619020 = 170309 + ?', 'answers' => ['448711']],
    ['text' => '4 : 50 = ?', 'answers' => ['0,08']],
    ['text' => '0,4 · ____ = 0,1', 'answers' => ['0,25', '1/4']],
    ['text' => '1/8 : 1/3 = ?', 'answers' => ['3/8']],
    ['text' => 'Verwandle 0,6 in einen gewöhnlichen Bruch.', 'answers' => ['6/10', '3/5']],
    ['text' => 'Ein Mechaniker hat aus 3 Teilen ein Gerät hergestellt. Die Einzelteile wiegen: 50 g, 6,4 kg, 1050 g. Wie viel wiegt das gesamte Gerät?', 'answers' => ['7500', '7500 g', '7500g', '7,5', '7,5Kg', '7,5kg', '7,5 Kg', '7,5 kg']],
    ['text' => 'Rechne um: Wie viel g sind 5 kg und 1 g?', 'answers' => ['5001', '5001g', '5001 g', '5001 G', '5001G']],
    ['text' => 'Wie viel Zinsen erbringen 1000 € zu 2 % in einem Jahr?', 'answers' => ['20', '20€', '20 €']],
    ['text' => 'Rudi kauft sich ein neues Mofa. Es kostet 1390 €. Bei Barzahlung bekommt er 3 % Rabatt. Wie viel muss er bezahlen?', 'answers' => ['1348,3', '1348,30', '1348,30 €', '1348,3 €', '1348,3€', '1348,30€']],
    ['text' => 'Im Sägewerk können aus einem Baumstamm 30 Bretter von 2 cm Dicke geschnitten werden. Wie viele Bretter erhält man, wenn sie 3 cm dick sind?', 'answers' => ['20', '20 Bretter']],
    ['text' => 'Im Sägewerk können aus einem Baumstamm 30 Bretter von 2 cm Dicke geschnitten werden. Wie dick wird ein Brett, wenn man 10 Bretter aus dem Stamm schneidet?', 'answers' => ['6', '6cm', '6 cm', '6 Cm', '6Cm']],
    ['text' => 'Berechne die Grundstücksgröße in m².', 'answers' => ['840', '840 m²', '840m²', '840 m2']],
    ['text' => 'Das Rad hat einen Durchmesser von 0,3 m. Welche Strecke legt es zurück, wenn es sich 100 mal dreht?', 'answers' => ['94,2', '94,2 m', '94,2m']],
    ['text' => '√49 = ?', 'answers' => ['7']],
    ['text' => '10⁴ = ?', 'answers' => ['10000']],
  ];

  protected static array $rohwertToPR = [
    1 => 0,
    2 => 0,
    3 => 2.5,
    4 => 2.5,
    5 => 5,
    6 => 8.5,
    7 => 16,
    8 => 26,
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
    ['pr' => 26, 't' => 43],
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
        'correct_answers' => $question['answers'],
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
    $answer = strtolower(trim($answer));
    return preg_replace('/[^0-9.,\/\-]/', '', $answer);
  }

  protected static function parseNumber(string $answer): ?float
  {
    $answer = str_replace(',', '.', $answer);
    if ($answer === '') {
      return null;
    }
    if (str_contains($answer, '/')) {
      [$num, $den] = array_map('trim', explode('/', $answer, 2));
      if ($num === '' || $den === '' || !is_numeric($num) || !is_numeric($den) || (float)$den == 0.0) {
        return null;
      }
      return (float)$num / (float)$den;
    }
    return is_numeric($answer) ? (float)$answer : null;
  }

  protected static function isCorrectAnswer(?string $userAnswer, array $validAnswers): bool
  {
    if ($userAnswer === null) {
      return false;
    }
    $normalizedUser = self::normalize($userAnswer);
    $userNumber = self::parseNumber($normalizedUser);

    foreach ($validAnswers as $valid) {
      $normalizedValid = self::normalize($valid);
      $validNumber = self::parseNumber($normalizedValid);
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
