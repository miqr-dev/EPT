<?php

namespace Tests\Unit;

use App\Models\Question;
use App\Models\Response;
use App\Models\TestSessionTest as TestSessionTestModel; // Alias for the TestSessionTest model
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_retrieve_response(): void
    {
        $response = Response::factory()->create();
        $this->assertDatabaseHas('responses', ['id' => $response->id]);

        $retrievedResponse = Response::find($response->id);
        $this->assertNotNull($retrievedResponse);
        $this->assertInstanceOf(Response::class, $retrievedResponse);
        $this->assertEquals($response->answer, $retrievedResponse->answer);
    }

    public function test_response_relationships(): void
    {
        // testSessionTest() (belongsTo)
        $testSessionTest = TestSessionTestModel::factory()->create();
        $responseForTestSessionTest = Response::factory()->for($testSessionTest, 'testSessionTest')->create();
        $this->assertInstanceOf(TestSessionTestModel::class, $responseForTestSessionTest->testSessionTest);

        // question() (belongsTo)
        $question = Question::factory()->create();
        $responseForQuestion = Response::factory()->for($question, 'question')->create();
        $this->assertInstanceOf(Question::class, $responseForQuestion->question);
    }
}
