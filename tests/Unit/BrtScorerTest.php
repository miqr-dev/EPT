<?php

namespace Tests\Unit;

use App\Services\BrtAScorer;
use App\Services\BrtBScorer;
use Tests\TestCase;

class BrtScorerTest extends TestCase
{
    public function test_brt_a_prefers_explicit_total_time_over_question_time_sum(): void
    {
        $result = BrtAScorer::score(
            array_fill(0, 16, ''),
            array_fill(0, 16, 1),
            35,
        );

        $this->assertSame(35, $result['total_time_seconds']);
        $this->assertSame(1, $result['answers'][0]['time_seconds']);
    }

    public function test_brt_b_falls_back_to_question_time_sum_without_explicit_total_time(): void
    {
        $result = BrtBScorer::score(
            array_fill(0, 16, ''),
            array_fill(0, 16, 2),
        );

        $this->assertSame(32, $result['total_time_seconds']);
    }
}
