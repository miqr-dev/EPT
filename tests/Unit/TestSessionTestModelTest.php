<?php

namespace Tests\Unit;

use App\Models\Response;
use App\Models\Test as TestModel;
use App\Models\TestSession;
use App\Models\TestSessionTest; // This is the model being tested
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestSessionTestModelTest extends TestCase // Renamed to avoid conflict if there was one
{
    use RefreshDatabase;

    public function test_can_create_and_retrieve_test_session_test(): void
    {
        $testSessionTest = TestSessionTest::factory()->create();
        $this->assertDatabaseHas('test_session_tests', ['id' => $testSessionTest->id]);

        $retrievedTestSessionTest = TestSessionTest::find($testSessionTest->id);
        $this->assertNotNull($retrievedTestSessionTest);
        $this->assertInstanceOf(TestSessionTest::class, $retrievedTestSessionTest);
        $this->assertEquals($testSessionTest->status, $retrievedTestSessionTest->status);
    }

    public function test_test_session_test_relationships(): void
    {
        $testSessionTest = TestSessionTest::factory()->create();

        // testSession() (belongsTo)
        $this->assertInstanceOf(TestSession::class, $testSessionTest->testSession);

        // test() (belongsTo, aliased as testModel)
        $this->assertInstanceOf(TestModel::class, $testSessionTest->test);

        // responses() (hasMany)
        Response::factory()->for($testSessionTest, 'testSessionTest')->count(3)->create();
        $testSessionTest->load('responses');
        $this->assertCount(3, $testSessionTest->responses);
        $this->assertInstanceOf(Response::class, $testSessionTest->responses->first());
    }
}
