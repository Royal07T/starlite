<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('student');

        $response = $this->postJson('/api/v1/login', [
            'email' => 'student@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['token', 'user' => ['id', 'name', 'email']],
            ]);
    }

    public function test_tutor_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'tutor@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('tutor');

        $response = $this->postJson('/api/v1/login', [
            'email' => 'tutor@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['token', 'user'],
            ]);
    }

    public function test_invalid_credentials_returns_401(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/v1/profile-settings/1');

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/profile-settings/{$user->id}");

        $response->assertOk();
    }

    public function test_user_cannot_access_other_users_profile(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Sanctum::actingAs($user1);

        $response = $this->getJson("/api/v1/profile-settings/{$user2->id}");

        $response->assertForbidden();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/logout');

        $response->assertOk();
    }

    public function test_nonexistent_route_returns_404(): void
    {
        $response = $this->getJson('/api/v1/nonexistent-endpoint');

        $response->assertNotFound()
            ->assertJson(['message' => __('general.api_url_not_found')]);
    }
}
