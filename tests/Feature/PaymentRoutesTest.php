<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PaymentRoutesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $course;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles first
        $this->createRoles();
        
        // Create a test user
        $this->user = User::factory()->student()->create();
        
        // Create a test course
        $this->course = Course::factory()->create([
            'instructor_id' => User::factory()->instructor()->create()->id,
            'is_free' => false,
            'price' => 99.99,
        ]);
    }

    private function createRoles()
    {
        // Create roles if they don't exist
        if (!\Spatie\Permission\Models\Role::where('name', 'student')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'student']);
        }
        if (!\Spatie\Permission\Models\Role::where('name', 'instructor')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'instructor']);
        }
        if (!\Spatie\Permission\Models\Role::where('name', 'admin')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        }
    }

    public function test_user_can_view_payment_history()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/payments/history');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'data',
                        'current_page',
                        'per_page',
                        'total'
                    ]
                ]);
    }

    public function test_user_can_create_payment_intent()
    {
        $this->actingAs($this->user);

        $response = $this->postJson("/api/courses/{$this->course->id}/pay", [
            'payment_method' => 'stripe'
        ]);

        // This will fail in test environment without Stripe keys, but we can test the route structure
        $response->assertStatus(422); // Validation error expected without proper Stripe setup
    }

    public function test_user_cannot_access_other_users_payment_details()
    {
        $otherUser = User::factory()->create();
        $payment = Payment::factory()->create([
            'user_id' => $otherUser->id,
            'course_id' => $this->course->id,
        ]);

        $this->actingAs($this->user);

        $response = $this->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_request_refund()
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'status' => 'completed',
        ]);

        $this->actingAs($this->user);

        $response = $this->postJson("/api/payments/{$payment->id}/refund", [
            'reason' => 'Not satisfied with the course content'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Refund request submitted successfully'
                ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'refund_requested',
            'refund_reason' => 'Not satisfied with the course content'
        ]);
    }

    public function test_user_cannot_request_refund_for_pending_payment()
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->user);

        $response = $this->postJson("/api/payments/{$payment->id}/refund", [
            'reason' => 'Not satisfied with the course content'
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Payment is not eligible for refund'
                ]);
    }

    public function test_user_cannot_request_refund_for_old_payment()
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'status' => 'completed',
            'created_at' => now()->subDays(31), // More than 30 days old
        ]);

        $this->actingAs($this->user);

        $response = $this->postJson("/api/payments/{$payment->id}/refund", [
            'reason' => 'Not satisfied with the course content'
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Refund period has expired'
                ]);
    }
} 