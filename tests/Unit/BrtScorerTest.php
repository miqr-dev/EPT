<?php

namespace Tests\Unit;

use App\Services\BrtAScorer;
use App\Services\BrtBScorer;
use PHPUnit\Framework\TestCase;

class BrtScorerTest extends TestCase
{
    public function test_brt_b_accepts_fraction_with_equivalent_decimal_annotation(): void
    {
        $answers = array_fill(0, 16, '');
        $answers[4] = '3/8 = 0,375';
        $times = array_fill(0, 16, 1);

        $result = BrtBScorer::score($answers, $times);

        $this->assertSame(1, $result['rohwert']);
        $this->assertTrue($result['answers'][4]['is_correct']);
    }

    public function test_brt_a_accepts_fraction_with_equivalent_decimal_annotation(): void
    {
        $answers = array_fill(0, 16, '');
        $answers[4] = '2/3 = 0,666';
        $times = array_fill(0, 16, 1);

        $result = BrtAScorer::score($answers, $times);

        $this->assertSame(1, $result['rohwert']);
        $this->assertTrue($result['answers'][4]['is_correct']);
    }
}
