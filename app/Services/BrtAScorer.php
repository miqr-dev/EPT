<?php

namespace App\Services;

class BrtAScorer
{
  protected static array $questions = [
    ['text' => '619020 – 541600 = ?', 'answers' => ['77420']],
    ['text' => '619020 = 174309 + ?', 'answers' => ['444711']],
    ['text' => '4 : 80 = ?', 'answers' => ['0,05']],
    ['text' => '0,2 · ____ = 0,1', 'answers' => ['0,5', '1/2']],
    ['text' => '1/3 : 1/2 = ?', 'answers' => ['2/3']],
    ['text' => 'Verwandle 0,4 in einen gewöhnlichen Bruch.', 'answers' => ['2/5', '4/10']],
    ['text' => 'Ein Mechaniker hat aus 3 Teilen ein Gerät hergestellt. Die Einzelteile wiegen: 50 g, 9,4 kg, 1050 g. Wie viel wiegt das gesamte Gerät?', 'answers' => ['10500', '10500 g', '10500g', '10,5', '10,5Kg', '10,5kg', '10,5 Kg', '10,5 kg']],
    ['text' => 'Rechne um: Wie viel g sind 9 kg und 1 g?', 'answers' => ['9001', '9001g', '9001 g', '9001 G', '9001G']],
    ['text' => 'Wie viel Zinsen erbringen 1000 € zu 4 % in einem Jahr?', 'answers' => ['40', '40€', '40 €']],
    ['text' => 'Rudi kauft sich ein neues Mofa. Es kostet 1390 €. Bei Barzahlung bekommt er 2 % Rabatt. Wie viel muss er bezahlen?', 'answers' => ['1362,2', '1362,20', '1362,20 €', '1362,2 €', '1362,2€', '1362,20€']],
    ['text' => 'Im Sägewerk können aus einem Baumstamm 20 Bretter von 3 cm Dicke geschnitten werden. Wie viele Bretter erhält man, wenn sie 2 cm dick sind?', 'answers' => ['30']],
    ['text' => 'Im Sägewerk können aus einem Baumstamm 20 Bretter von 3 cm Dicke geschnitten werden. Wie dick wird ein Brett, wenn man 15 Bretter aus dem Stamm schneidet?', 'answers' => ['4', '4cm', '4 cm', '4 Cm', '4Cm']],
    ['text' => 'Berechne die Grundstücksgröße in m².', 'answers' => ['930', '930 m²', '930m²', '930 m2']],
    ['text' => 'Das Rad hat einen Durchmesser von 0,6 m. Welche Strecke legt es zurück, wenn es sich 100 mal dreht?', 'answers' => ['188,4', '188,4 m', '188,4m']],
    ['text' => '√81 = ?', 'answers' => ['9']],
    ['text' => '10³ = ?', 'answers' => ['1000']],
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
    $userCandidates = self::extractNumericCandidates($normalizedUser);
    $userNumber = self::parseNumber($normalizedUser);
    if ($userNumber !== null) {
      $userCandidates[] = $userNumber;
    }

    foreach ($validAnswers as $valid) {
      $normalizedValid = self::normalize($valid);
      $validNumber = self::parseNumber($normalizedValid);
      if ($validNumber !== null && count($userCandidates) > 0) {
        foreach ($userCandidates as $candidate) {
          if (abs($candidate - $validNumber) < 0.001) {
            return true;
          }
        }
      } elseif ($normalizedUser === $normalizedValid) {
        return true;
      }
    }
    return false;
  }

  /**
   * @return float[]
   */
  protected static function extractNumericCandidates(string $answer): array
  {
    preg_match_all('/-?\d+(?:[.,]\d+)?(?:\/-?\d+(?:[.,]\d+)?)?/', $answer, $matches);
    $numbers = [];
    foreach ($matches[0] ?? [] as $token) {
      $value = self::parseNumber($token);
      if ($value !== null) {
        $numbers[] = $value;
      }
    }
    return $numbers;
  }
}
