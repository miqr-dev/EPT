<?php

namespace Database\Factories;

use App\Models\Test; // Moved use statement
use App\Models\TestBattery; // Moved use statement
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TestBatteryTest>
 */
class TestBatteryTestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'test_battery_id' => TestBattery::factory(),
            'test_id' => Test::factory(),
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
