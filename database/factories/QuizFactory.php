<?php
// database/factories/QuizFactory.php
namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3) . ' Quiz',
            'description' => fake()->paragraph(2),
            'time_limit' => fake()->randomElement([null, 15, 30, 45, 60]),
            'max_attempts' => fake()->numberBetween(1, 5),
            'passing_score' => fake()->randomElement([60, 70, 75, 80]),
            'show_results_immediately' => fake()->boolean(80),
            'course_id' => Course::factory(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function ($quiz) {
            // Create questions for the quiz
            \App\Models\QuizQuestion::factory(fake()->numberBetween(5, 15))->create([
                'quiz_id' => $quiz->id,
            ]);
        });
    }
}
