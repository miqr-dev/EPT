<?php

namespace Database\Factories;

use App\Models\Test; // Moved use statement
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['numeric', 'text', 'single_choice', 'multiple_choice']);
        $options = null;
        if ($type === 'single_choice' || $type === 'multiple_choice') {
            $options = json_encode([$this->faker->word => $this->faker->sentence, $this->faker->word => $this->faker->sentence]);
        }

        return [
            'test_id' => Test::factory(),
            'order' => $this->faker->numberBetween(1, 10),
            'type' => $type,
            'text' => $this->faker->paragraph,
            'image_path' => null,
            'options' => $options,
            'correct_answer' => $this->faker->word,
            'points' => $this->faker->numberBetween(1, 10),
        ];
    }
}
