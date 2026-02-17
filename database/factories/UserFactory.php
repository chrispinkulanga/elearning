<?php
// database/factories/UserFactory.php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake()->phoneNumber(),
            'bio' => fake()->paragraph(3),
            'status' => fake()->randomElement(['active', 'inactive']),
            'social_links' => [
                'facebook' => fake()->url(),
                'twitter' => fake()->url(),
                'linkedin' => fake()->url(),
            ],
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is a student.
     */
    public function student(): static
    {
        return $this->afterCreating(function ($user) {
            $user->assignRole('student');
        });
    }

    /**
     * Indicate that the user is an instructor.
     */
    public function instructor(): static
    {
        return $this->afterCreating(function ($user) {
            $user->assignRole('instructor');
        });
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->afterCreating(function ($user) {
            $user->assignRole('admin');
        });
    }

    /**
     * Indicate that the question should be true/false.
     */
    public function trueFalse(): static
    {
        return $this->state([
            'type' => 'true_false',
            'options' => ['True', 'False'],
            'correct_answers' => [fake()->randomElement(['True', 'False'])],
        ]);
    }

    /**
     * Indicate that the question should be short answer.
     */
    public function shortAnswer(): static
    {
        return $this->state([
            'type' => 'short_answer',
            'options' => null,
            'correct_answers' => [fake()->word()],
        ]);
    }
}
