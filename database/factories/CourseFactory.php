<?php
// database/factories/CourseFactory.php
namespace Database\Factories;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);
        $isFree = fake()->boolean(20); // 20% chance of being free

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraphs(5, true),
            'short_description' => fake()->sentence(12),
            'price' => $isFree ? 0 : fake()->randomFloat(2, 9.99, 299.99),
            'discounted_price' => fake()->boolean(30) ? fake()->randomFloat(2, 5.99, 199.99) : null,
            'level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'tags' => fake()->words(5),
            'status' => fake()->randomElement(['draft', 'pending', 'approved', 'rejected']),
            'is_free' => $isFree,
            'is_featured' => fake()->boolean(15), // 15% chance of being featured
            'access_type' => fake()->randomElement(['lifetime', 'limited']),
            'access_days' => fake()->boolean(30) ? fake()->numberBetween(30, 365) : null,
            'duration_hours' => fake()->numberBetween(5, 100),
            'requirements' => fake()->sentences(3),
            'outcomes' => fake()->sentences(4),
            'instructor_id' => User::factory()->instructor(),
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
        ];
    }

    /**
     * Indicate that the course should be approved.
     */
    public function approved(): static
    {
        return $this->state([
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the course should be pending approval.
     */
    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the course should be rejected.
     */
    public function rejected(): static
    {
        return $this->state([
            'status' => 'rejected',
        ]);
    }

    /**
     * Indicate that the course should be free.
     */
    public function free(): static
    {
        return $this->state([
            'price' => 0,
            'discounted_price' => null,
            'is_free' => true,
        ]);
    }

    /**
     * Indicate that the course should be paid.
     */
    public function paid(): static
    {
        return $this->state([
            'price' => fake()->randomFloat(2, 19.99, 199.99),
            'is_free' => false,
        ]);
    }

    /**
     * Indicate that the course should be featured.
     */
    public function featured(): static
    {
        return $this->state([
            'is_featured' => true,
            'status' => 'approved', // Featured courses should be approved
        ]);
    }

    /**
     * Indicate that the course should be for beginners.
     */
    public function beginner(): static
    {
        return $this->state([
            'level' => 'beginner',
        ]);
    }

    /**
     * Indicate that the course should be intermediate level.
     */
    public function intermediate(): static
    {
        return $this->state([
            'level' => 'intermediate',
        ]);
    }

    /**
     * Indicate that the course should be advanced level.
     */
    public function advanced(): static
    {
        return $this->state([
            'level' => 'advanced',
        ]);
    }

    /**
     * Indicate that the course should have limited access.
     */
    public function limitedAccess(): static
    {
        return $this->state([
            'access_type' => 'limited',
            'access_days' => fake()->numberBetween(30, 365),
        ]);
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function ($course) {
            // Create lessons for the course
            \App\Models\Lesson::factory(fake()->numberBetween(5, 15))->create([
                'course_id' => $course->id,
            ]);

            // Create a quiz for the course
            \App\Models\Quiz::factory()->create([
                'course_id' => $course->id,
            ]);

            // Create a forum for the course
            \App\Models\Forum::factory()->create([
                'course_id' => $course->id,
            ]);
        });
    }
}
