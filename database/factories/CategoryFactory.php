<?php
// database/factories/CategoryFactory.php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Web Development',
            'Mobile Development',
            'Data Science',
            'Machine Learning',
            'Digital Marketing',
            'Graphic Design',
            'UI/UX Design',
            'Photography',
            'Video Editing',
            'Music Production',
            'Business',
            'Finance',
            'Health & Fitness',
            'Cooking',
            'Language Learning',
            'Personal Development',
            'Art & Crafts',
            'Writing',
            'Mathematics',
            'Science',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(2),
            'is_active' => fake()->boolean(90), // 90% chance of being active
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the category should be active.
     */
    public function active(): static
    {
        return $this->state([
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the category should be inactive.
     */
    public function inactive(): static
    {
        return $this->state([
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the category should be popular (with courses).
     */
    public function popular(): static
    {
        return $this->afterCreating(function ($category) {
            // Create multiple courses for this category
            \App\Models\Course::factory(fake()->numberBetween(5, 15))->create([
                'category_id' => $category->id,
            ]);
        });
    }
}