<?php

namespace Tests\Unit;

use App\Services\BrtAScorer;
use App\Services\BrtBScorer;
use Tests\TestCase;

class BrtScorerTest extends TestCase
{
    public function test_brt_a_adds_correct_answers(): void
    {
        $result = BrtAScorer::score([], []);
        $result = BrtAScorer::addCorrectAnswers($result);

        $this->assertArrayHasKey('answers', $result);
        $this->assertArrayHasKey('correct_answers', $result['answers'][0]);
        $this->assertEquals(['77420'], $result['answers'][0]['correct_answers']);
    }

    public function test_brt_b_adds_correct_answers(): void
    {
        $result = BrtBScorer::score([], []);
        $result = BrtBScorer::addCorrectAnswers($result);

        $this->assertArrayHasKey('answers', $result);
        $this->assertArrayHasKey('correct_answers', $result['answers'][0]);
        $this->assertEquals(['277420'], $result['answers'][0]['correct_answers']);
    }
}
