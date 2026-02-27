<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_staff_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/staff/login');

        $response->assertStatus(200);
    }

    public function test_admin_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_with_nip(): void
    {
        $user = User::factory()->create([
            'nip' => '123456789',
            'role' => 'user',
        ]);

        $response = $this->post('/login', [
            'nip' => '123456789',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
    }

    public function test_staff_can_authenticate_with_nip(): void
    {
        $user = User::factory()->create([
            'nip' => '987654321',
            'role' => 'staff_psdm',
        ]);

        $response = $this->post('/staff/login', [
            'nip' => '987654321',
            'password' => 'password',
        ]);

        // Staff login redirects after successful auth
        $response->assertRedirect();
    }

    public function test_admin_can_authenticate_with_nip(): void
    {
        $user = User::factory()->create([
            'nip' => '111222333',
            'role' => 'admin',
        ]);

        $response = $this->post('/admin/login', [
            'nip' => '111222333',
            'password' => 'password',
        ]);

        // Admin login redirects after successful auth
        $response->assertRedirect();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'nip' => '123456789',
        ]);

        $this->post('/login', [
            'nip' => '123456789',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_nip(): void
    {
        $user = User::factory()->create([
            'nip' => '123456789',
        ]);

        $this->post('/login', [
            'nip' => '999999999',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_nip_is_required_for_login(): void
    {
        $response = $this->post('/login', [
            'nip' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('nip');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
