<?php
// database/factories/LessonFactory.php
namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['video', 'text', 'quiz', 'assignment']);
        
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(2),
            'type' => $type,
            'video_url' => $type === 'video' ? fake()->url() : null,
            'video_duration' => $type === 'video' ? fake()->numberBetween(300, 3600) : null,
            'content' => in_array($type, ['text', 'assignment']) ? fake()->paragraphs(5, true) : null,
            'attachments' => fake()->boolean(30) ? [fake()->url(), fake()->url()] : null,
            'is_preview' => fake()->boolean(10), // 10% chance of being preview
            'sort_order' => fake()->numberBetween(1, 20),
            'course_id' => Course::factory(),
        ];
    }

    /**
     * Indicate that the lesson should be a video.
     */
    public function video(): static
    {
        return $this->state([
            'type' => 'video',
            'video_url' => fake()->url(),
            'video_duration' => fake()->numberBetween(300, 3600),
            'content' => null,
        ]);
    }

    /**
     * Indicate that the lesson should be text-based.
     */
    public function text(): static
    {
        return $this->state([
            'type' => 'text',
            'video_url' => null,
            'video_duration' => null,
            'content' => fake()->paragraphs(5, true),
        ]);
    }

    /**
     * Indicate that the lesson should be a preview.
     */
    public function preview(): static
    {
        return $this->state([
            'is_preview' => true,
        ]);
    }
}
