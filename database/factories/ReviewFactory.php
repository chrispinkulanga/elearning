<?php
// database/factories/ReviewFactory.php
namespace Database\Factories;

use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rating = fake()->numberBetween(1, 5);
        
        // Generate comment based on rating
        $comments = [
            1 => [
                'Very disappointed with this course. Poor quality content.',
                'Not worth the money. Expected much better.',
                'Confusing explanations and outdated material.'
            ],
            2 => [
                'Below average course. Some good points but overall disappointing.',
                'Content is okay but presentation could be much better.',
                'Had higher expectations based on the description.'
            ],
            3 => [
                'Average course. Nothing special but covers the basics.',
                'Decent content but could use more practical examples.',
                'Good for beginners but lacks depth.'
            ],
            4 => [
                'Very good course! Learned a lot and well structured.',
                'Great content and clear explanations. Highly recommend.',
                'Excellent instructor with practical examples.',
                'Well worth the investment. Good quality material.'
            ],
            5 => [
                'Outstanding course! Exceeded all my expectations.',
                'Perfect blend of theory and practice. Amazing instructor!',
                'Best course I have taken so far. Five stars!',
                'Comprehensive material and excellent presentation.',
                'Life-changing course. Highly recommended to everyone!'
            ]
        ];

        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'rating' => $rating,
            'comment' => fake()->randomElement($comments[$rating]),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
        ];
    }

    /**
     * Indicate that the review should be approved.
     */
    public function approved(): static
    {
        return $this->state([
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the review should be pending.
     */
    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the review should have a high rating.
     */
    public function positive(): static
    {
        return $this->state([
            'rating' => fake()->numberBetween(4, 5),
        ]);
    }

    /**
     * Indicate that the review should have a low rating.
     */
    public function negative(): static
    {
        return $this->state([
            'rating' => fake()->numberBetween(1, 2),
        ]);
    }
}
