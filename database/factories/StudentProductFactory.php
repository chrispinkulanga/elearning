<?php
// database/factories/StudentProductFactory.php
namespace Database\Factories;

use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentProduct>
 */
class StudentProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(3, true),
            'images' => fake()->boolean(70) ? [
                'student-products/images/' . fake()->image(),
                'student-products/images/' . fake()->image()
            ] : null,
            'video_url' => fake()->boolean(40) ? fake()->url() : null,
            'files' => fake()->boolean(30) ? [
                [
                    'name' => fake()->word() . '.pdf',
                    'path' => 'student-products/files/' . fake()->word() . '.pdf',
                    'size' => fake()->numberBetween(1000, 5000000)
                ]
            ] : null,
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'rating' => fake()->randomFloat(2, 0, 5),
            'views' => fake()->numberBetween(0, 1000),
        ];
    }

    /**
     * Indicate that the product should be approved.
     */
    public function approved(): static
    {
        return $this->state([
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the product should be pending.
     */
    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
        ]);
    }
}
