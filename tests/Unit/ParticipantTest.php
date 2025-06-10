<?php

namespace Tests\Unit;

use App\Models\Participant;
use App\Models\TestSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_retrieve_participant(): void
    {
        $participant = Participant::factory()->create();
        $this->assertDatabaseHas('participants', ['id' => $participant->id]);

        $retrievedParticipant = Participant::find($participant->id);
        $this->assertNotNull($retrievedParticipant);
        $this->assertInstanceOf(Participant::class, $retrievedParticipant);
        $this->assertEquals($participant->first_name, $retrievedParticipant->first_name);
        $this->assertEquals($participant->last_name, $retrievedParticipant->last_name);
    }

    public function test_participant_relationships(): void
    {
        $participant = Participant::factory()->create();

        // Test TestSessions relationship (hasMany)
        TestSession::factory()->for($participant)->count(2)->create();

        $participant->load('testSessions'); // Eager load

        $this->assertCount(2, $participant->testSessions);
        $this->assertInstanceOf(TestSession::class, $participant->testSessions->first());
    }
}
