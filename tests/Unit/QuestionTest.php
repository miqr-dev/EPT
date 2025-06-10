<?php

namespace Tests\Unit;

use App\Models\Question;
use App\Models\Response;
use App\Models\Test as TestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_retrieve_question(): void
    {
        $question = Question::factory()->create();
        $this->assertDatabaseHas('questions', ['id' => $question->id]);

        $retrievedQuestion = Question::find($question->id);
        $this->assertNotNull($retrievedQuestion);
        $this->assertInstanceOf(Question::class, $retrievedQuestion);
        $this->assertEquals($question->text, $retrievedQuestion->text);
    }

    public function test_question_relationships(): void
    {
        // test() (belongsTo)
        $testModel = TestModel::factory()->create();
        $question = Question::factory()->for($testModel, 'test')->create();
        $this->assertInstanceOf(TestModel::class, $question->test);

        // responses() (hasMany)
        $questionWithResponses = Question::factory()->create();
        Response::factory()->for($questionWithResponses, 'question')->count(3)->create();
        $questionWithResponses->load('responses');
        $this->assertCount(3, $questionWithResponses->responses);
        $this->assertInstanceOf(Response::class, $questionWithResponses->responses->first());
    }
}
