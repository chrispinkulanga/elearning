<?php
// database/factories/ForumTopicFactory.php
namespace Database\Factories;

use App\Models\Forum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ForumTopic>
 */
class ForumTopicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(fake()->numberBetween(3, 8)),
            'content' => fake()->paragraphs(fake()->numberBetween(2, 5), true),
            'user_id' => User::factory(),
            'forum_id' => Forum::factory(),
            'is_pinned' => fake()->boolean(5), // 5% chance of being pinned
            'is_locked' => fake()->boolean(2), // 2% chance of being locked
            'views' => fake()->numberBetween(0, 1000),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function ($topic) {
            // Create some replies for the topic
            \App\Models\ForumReply::factory(fake()->numberBetween(0, 5))->create([
                'topic_id' => $topic->id,
            ]);
        });
    }
}