<?php

namespace Database\Factories;

use App\Models\Admin; // Moved use statement
use App\Models\TestSession; // Moved use statement
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'test_session_id' => TestSession::factory(),
            'admin_id' => Admin::factory(),
            'note' => $this->faker->paragraph,
        ];
    }
}
