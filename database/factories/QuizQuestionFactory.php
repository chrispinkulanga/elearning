<?php

namespace Database\Factories;

use App\Models\QuizQuestion;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuizQuestion>
 */
class QuizQuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = QuizQuestion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['multiple_choice', 'true_false', 'short_answer']);
        
        $options = null;
        $correctAnswers = [];
        
        if ($type === 'multiple_choice') {
            $options = [
                $this->faker->sentence(),
                $this->faker->sentence(),
                $this->faker->sentence(),
                $this->faker->sentence(),
            ];
            $correctAnswers = [$this->faker->numberBetween(0, 3)];
        } elseif ($type === 'true_false') {
            $options = ['True', 'False'];
            $correctAnswers = [$this->faker->randomElement(['True', 'False'])];
        } else {
            $correctAnswers = [$this->faker->word()];
        }

        return [
            'quiz_id' => Quiz::factory(),
            'question' => $this->faker->sentence() . '?',
            'type' => $type,
            'options' => $options,
            'correct_answers' => $correctAnswers,
            'points' => $this->faker->numberBetween(1, 10),
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the question is multiple choice.
     */
    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'multiple_choice',
            'options' => [
                $this->faker->sentence(),
                $this->faker->sentence(),
                $this->faker->sentence(),
                $this->faker->sentence(),
            ],
            'correct_answers' => [$this->faker->numberBetween(0, 3)],
        ]);
    }

    /**
     * Indicate that the question is true/false.
     */
    public function trueFalse(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'true_false',
            'options' => ['True', 'False'],
            'correct_answers' => [$this->faker->randomElement(['True', 'False'])],
        ]);
    }

    /**
     * Indicate that the question is short answer.
     */
    public function shortAnswer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'short_answer',
            'options' => null,
            'correct_answers' => [$this->faker->word()],
        ]);
    }
}
