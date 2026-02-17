<?php
// database/factories/ForumReplyFactory.php
namespace Database\Factories;

use App\Models\ForumTopic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ForumReply>
 */
class ForumReplyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => fake()->paragraphs(fake()->numberBetween(1, 3), true),
            'user_id' => User::factory(),
            'topic_id' => ForumTopic::factory(),
            'parent_id' => null, // Most replies are top-level
            'upvotes' => fake()->numberBetween(0, 50),
        ];
    }

    /**
     * Indicate that the reply should be a nested reply.
     */
    public function nested(): static
    {
        return $this->state([
            'parent_id' => \App\Models\ForumReply::factory(),
        ]);
    }
}
