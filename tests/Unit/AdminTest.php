<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Models\Note;
use App\Models\TestBattery;
use App\Models\TestSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_retrieve_admin(): void
    {
        $admin = Admin::factory()->create();
        $this->assertDatabaseHas('admins', ['id' => $admin->id]);

        $retrievedAdmin = Admin::find($admin->id);
        $this->assertNotNull($retrievedAdmin);
        $this->assertInstanceOf(Admin::class, $retrievedAdmin);
        $this->assertEquals($admin->email, $retrievedAdmin->email);
    }

    public function test_admin_relationships(): void
    {
        $admin = Admin::factory()->create();

        // Test TestBatteries relationship
        TestBattery::factory()->for($admin)->count(2)->create();
        $admin->load('testBatteries'); // Eager load
        $this->assertCount(2, $admin->testBatteries);
        $this->assertInstanceOf(TestBattery::class, $admin->testBatteries->first());

        // Test TestSessions relationship
        TestSession::factory()->for($admin)->count(3)->create();
        $admin->load('testSessions'); // Eager load
        $this->assertCount(3, $admin->testSessions);
        $this->assertInstanceOf(TestSession::class, $admin->testSessions->first());

        // Test Notes relationship
        Note::factory()->for($admin)->count(4)->create();
        $admin->load('notes'); // Eager load
        $this->assertCount(4, $admin->notes);
        $this->assertInstanceOf(Note::class, $admin->notes->first());
    }
}
