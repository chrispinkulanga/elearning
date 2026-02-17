<?php
// database/factories/EnrollmentFactory.php
namespace Database\Factories;

use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $enrolledAt = fake()->dateTimeBetween('-6 months', 'now');
        
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'amount_paid' => fake()->randomFloat(2, 0, 299.99),
            'status' => fake()->randomElement(['active', 'completed', 'cancelled']),
            'enrolled_at' => $enrolledAt,
            'expires_at' => fake()->boolean(30) ? fake()->dateTimeBetween($enrolledAt, '+1 year') : null,
            'progress_percentage' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the enrollment should be active.
     */
    public function active(): static
    {
        return $this->state([
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the enrollment should be completed.
     */
    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'progress_percentage' => 100,
        ]);
    }

    /**
     * Indicate that the enrollment should be for a free course.
     */
    public function free(): static
    {
        return $this->state([
            'amount_paid' => 0,
        ]);
    }
}
