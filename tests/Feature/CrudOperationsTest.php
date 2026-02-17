<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CrudOperationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration (Create)
     */
    public function test_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    /**
     * Test user login (Read/Authentication)
     */
    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure(['token', 'user']);
    }

    /**
     * Test fetching courses (Read)
     */
    public function test_can_fetch_courses()
    {
        $category = Category::factory()->create();
        $course = Course::factory()->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/courses');

        $response->assertStatus(200)
                ->assertJsonStructure(['data' => ['*' => ['id', 'title', 'slug']]]);
    }

    /**
     * Test updating user profile (Update)
     */
    public function test_authenticated_user_can_update_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->putJson('/api/user/profile', [
                            'name' => 'Updated Name',
                            'bio' => 'Updated bio',
                        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Test creating course (Create - Instructor)
     */
    public function test_instructor_can_create_course()
    {
        $user = User::factory()->create();
        $user->assignRole('instructor');
        $category = Category::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/instructor/courses', [
                            'title' => 'New Course',
                            'description' => 'Course description',
                            'category_id' => $category->id,
                            'price' => 99.99,
                            'level' => 'beginner',
                        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('courses', ['title' => 'New Course']);
    }

    /**
     * Test deleting course (Delete - Admin)
     */
    public function test_admin_can_delete_course()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $category = Category::factory()->create();
        $course = Course::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin, 'sanctum')
                        ->deleteJson("/api/admin/courses/{$course->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }
}
