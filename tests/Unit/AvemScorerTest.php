<?php

use App\Services\AvemScorer;

test('avem scorer calculates category scores', function () {
    $answers = [];
    for ($i = 1; $i <= 66; $i++) {
        $answers[] = ['number' => $i, 'answer' => 3];
    }

    $result = AvemScorer::score($answers);
    expect($result['category_scores'])->toBe([
        'SBA' => 18,
        'BE' => 18,
        'VB' => 18,
        'PS' => 18,
        'DF' => 18,
        'RT' => 18,
        'OP' => 18,
        'IR' => 18,
        'EB' => 18,
        'LZ' => 18,
        'SU' => 18,
    ]);
});

