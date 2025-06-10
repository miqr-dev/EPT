<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Models\Note;
use App\Models\Participant;
use App\Models\TestBattery;
use App\Models\TestSession;
use App\Models\TestSessionTest as TestSessionTestModel; // Alias for the model
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestSessionTest extends TestCase // This class tests the TestSession model
{
    use RefreshDatabase;

    public function test_can_create_and_retrieve_test_session(): void
    {
        $testSession = TestSession::factory()->create();
        $this->assertDatabaseHas('test_sessions', ['id' => $testSession->id]);

        $retrievedTestSession = TestSession::find($testSession->id);
        $this->assertNotNull($retrievedTestSession);
        $this->assertInstanceOf(TestSession::class, $retrievedTestSession);
        $this->assertEquals($testSession->status, $retrievedTestSession->status);
    }

    public function test_test_session_relationships(): void
    {
        $testSession = TestSession::factory()->create();

        // participant() (belongsTo)
        $this->assertInstanceOf(Participant::class, $testSession->participant);

        // testBattery() (belongsTo)
        $this->assertInstanceOf(TestBattery::class, $testSession->testBattery);

        // admin() (belongsTo)
        $this->assertInstanceOf(Admin::class, $testSession->admin);

        // testSessionTests() (hasMany)
        TestSessionTestModel::factory()->for($testSession, 'testSession')->count(2)->create();
        $testSession->load('testSessionTests');
        $this->assertCount(2, $testSession->testSessionTests);
        $this->assertInstanceOf(TestSessionTestModel::class, $testSession->testSessionTests->first());

        // notes() (hasMany)
        Note::factory()->for($testSession, 'testSession')->count(3)->create();
        $testSession->load('notes');
        $this->assertCount(3, $testSession->notes);
        $this->assertInstanceOf(Note::class, $testSession->notes->first());
    }
}
