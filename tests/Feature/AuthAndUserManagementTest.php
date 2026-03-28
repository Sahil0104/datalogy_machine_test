<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_and_register_pages(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_registered_user_is_redirected_away_from_guest_pages(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect('/dashboard');

        $this->actingAs($user)
            ->get('/register')
            ->assertRedirect('/dashboard');
    }

    public function test_user_can_register_and_get_redirected_to_dashboard(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_login_shows_error_and_does_not_authenticate(): void
    {
        User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_manage_users_via_ajax_routes(): void
    {
        $admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
        ]);

        $this->actingAs($admin);

        $createResponse = $this->postJson('/users', [
            'first_name' => 'New',
            'last_name' => 'Person',
            'email' => 'new@example.com',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('message', 'User added successfully.');

        $userId = User::where('email', 'new@example.com')->value('id');

        $this->putJson("/users/{$userId}", [
            'first_name' => 'Updated',
            'last_name' => 'Person',
            'email' => 'updated@example.com',
        ])->assertOk()
            ->assertJsonPath('message', 'User updated successfully.');

        $this->getJson('/users/list')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->deleteJson("/users/{$userId}")
            ->assertOk()
            ->assertJsonPath('message', 'User deleted successfully.');
    }
}
