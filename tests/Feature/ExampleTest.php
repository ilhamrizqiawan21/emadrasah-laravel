<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_active_user_can_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'is_active' => true,
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.test',
            'password' => Hash::make('password'),
            'is_active' => false,
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@example.test',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_access_users_index(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/users');

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_users_index(): void
    {
        $operator = User::factory()->create([
            'role' => 'operator',
            'is_active' => true,
        ]);

        $response = $this->actingAs($operator)->get('/users');

        $response->assertForbidden();
    }
}
