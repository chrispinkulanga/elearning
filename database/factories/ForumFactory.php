<?php
// database/factories/ForumFactory.php
namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Forum>
 */
class ForumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement([
                'General Discussion',
                'Q&A Forum',
                'Project Showcase',
                'Study Group',
                'Help & Support'
            ]),
            'description' => fake()->paragraph(2),
            'course_id' => Course::factory(),
            'is_active' => fake()->boolean(95), // 95% chance of being active
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function ($forum) {
            // Create some topics for the forum
            \App\Models\ForumTopic::factory(fake()->numberBetween(2, 8))->create([
                'forum_id' => $forum->id,
            ]);
        });
    }
}
