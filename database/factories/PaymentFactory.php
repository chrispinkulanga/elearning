<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => Str::uuid(),
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'currency' => 'USD',
            'payment_method' => $this->faker->randomElement(['stripe', 'paypal']),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'gateway_transaction_id' => $this->faker->regexify('[A-Za-z0-9]{20}'),
            'gateway_response' => [
                'id' => $this->faker->regexify('[A-Za-z0-9]{20}'),
                'status' => 'succeeded',
                'amount' => $this->faker->numberBetween(1000, 50000),
            ],
        ];
    }

    /**
     * Indicate that the payment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the payment is failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }

    /**
     * Indicate that the payment is refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
            'refund_amount' => $this->faker->randomFloat(2, 10, 500),
            'refunded_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the payment has a refund request.
     */
    public function refundRequested(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refund_requested',
            'refund_reason' => $this->faker->sentence(),
            'refund_requested_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }
} 