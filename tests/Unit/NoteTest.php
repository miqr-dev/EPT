<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Models\Note;
use App\Models\TestSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_retrieve_note(): void
    {
        $note = Note::factory()->create();
        $this->assertDatabaseHas('notes', ['id' => $note->id]);

        $retrievedNote = Note::find($note->id);
        $this->assertNotNull($retrievedNote);
        $this->assertInstanceOf(Note::class, $retrievedNote);
        $this->assertEquals($note->note, $retrievedNote->note);
    }

    public function test_note_relationships(): void
    {
        // testSession() (belongsTo)
        $testSession = TestSession::factory()->create();
        $noteForTestSession = Note::factory()->for($testSession, 'testSession')->create();
        $this->assertInstanceOf(TestSession::class, $noteForTestSession->testSession);

        // admin() (belongsTo)
        $admin = Admin::factory()->create();
        $noteForAdmin = Note::factory()->for($admin)->create();
        $this->assertInstanceOf(Admin::class, $noteForAdmin->admin);
    }
}
