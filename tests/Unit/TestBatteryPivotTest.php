<?php

namespace Tests\Unit;

use App\Models\Test as TestModel;
use App\Models\TestBattery;
use App\Models\TestBatteryTest; // This is the pivot model
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestBatteryPivotTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_retrieve_test_battery_pivot_entry(): void
    {
        // The factory 'TestBatteryTestFactory' should create associated Test and TestBattery
        $pivotEntry = TestBatteryTest::factory()->create();

        // Assert that the pivot entry exists in the 'test_battery_tests' table
        // Note: TestBatteryTest model has $table = 'test_battery_tests'
        // We can't rely on $pivotEntry->id directly after factory creation for Pivot models sometimes
        $this->assertDatabaseHas('test_battery_tests', [
            'test_battery_id' => $pivotEntry->test_battery_id,
            'test_id' => $pivotEntry->test_id,
            'order' => $pivotEntry->order,
        ]);

        // Retrieve the entry based on other attributes to get the ID
        $retrievedEntry = TestBatteryTest::where('test_battery_id', $pivotEntry->test_battery_id)
                                        ->where('test_id', $pivotEntry->test_id)
                                        ->where('order', $pivotEntry->order)
                                        ->first();
        $this->assertNotNull($retrievedEntry);
        $this->assertInstanceOf(TestBatteryTest::class, $retrievedEntry);
        $this->assertEquals($pivotEntry->order, $retrievedEntry->order);
    }

    public function test_test_battery_pivot_relationships(): void
    {
        $pivotEntry = TestBatteryTest::factory()->create();

        // Test TestBattery relationship (belongsTo)
        $this->assertInstanceOf(TestBattery::class, $pivotEntry->testBattery);

        // Test Test relationship (belongsTo)
        $this->assertInstanceOf(TestModel::class, $pivotEntry->test);
    }
}
