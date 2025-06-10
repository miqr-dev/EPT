<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Models\Test as TestModel;
use App\Models\TestBattery;
use App\Models\TestSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestBatteryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_retrieve_test_battery(): void
    {
        $testBattery = TestBattery::factory()->create();
        $this->assertDatabaseHas('test_batteries', ['id' => $testBattery->id]);

        $retrievedTestBattery = TestBattery::find($testBattery->id);
        $this->assertNotNull($retrievedTestBattery);
        $this->assertInstanceOf(TestBattery::class, $retrievedTestBattery);
        $this->assertEquals($testBattery->title, $retrievedTestBattery->title);
    }

    public function test_test_battery_relationships(): void
    {
        // admin() (belongsTo)
        $admin = Admin::factory()->create();
        $testBatteryWithAdmin = TestBattery::factory()->for($admin)->create();
        $this->assertInstanceOf(Admin::class, $testBatteryWithAdmin->admin);

        // tests() (belongsToMany)
        $testBatteryForTests = TestBattery::factory()->create();
        $testModel = TestModel::factory()->create();
        $order = 1;
        $testBatteryForTests->tests()->attach($testModel->id, ['order' => $order]);

        $this->assertCount(1, $testBatteryForTests->tests);
        $this->assertInstanceOf(TestModel::class, $testBatteryForTests->tests->first());
        $this->assertEquals($order, $testBatteryForTests->tests->first()->pivot->order);

        // testSessions() (hasMany)
        $testBatteryWithSessions = TestBattery::factory()->create();
        TestSession::factory()->for($testBatteryWithSessions, 'testBattery')->count(2)->create();
        $testBatteryWithSessions->load('testSessions');
        $this->assertCount(2, $testBatteryWithSessions->testSessions);
        $this->assertInstanceOf(TestSession::class, $testBatteryWithSessions->testSessions->first());
    }
}
