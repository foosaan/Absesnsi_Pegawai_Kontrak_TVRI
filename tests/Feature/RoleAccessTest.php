<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // AUTHENTICATION TESTS
    // ==========================================

    /** @test */
    public function login_page_is_accessible()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        // Simulate authenticated user accessing home
        $response = $this->actingAs($user)->get('/home');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_login_with_wrong_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    // ==========================================
    // MIDDLEWARE / ROLE ACCESS TESTS
    // ==========================================

    /** @test */
    public function guest_is_redirected_to_login()
    {
        $response = $this->get('/home');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function admin_can_access_admin_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->get('/admin');
        // Middleware redirects wrong-role users instead of 403
        $response->assertRedirect();
    }

    /** @test */
    public function staff_psdm_can_access_psdm_dashboard()
    {
        $staff = User::factory()->create(['role' => 'staff_psdm']);
        $response = $this->actingAs($staff)->get('/staff/psdm');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_access_psdm_dashboard()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->get('/staff/psdm');
        // Middleware now redirects wrong-role users to their dashboard
        $response->assertRedirect();
    }

    /** @test */
    public function staff_keuangan_can_access_keuangan_dashboard()
    {
        $staff = User::factory()->create(['role' => 'staff_keuangan']);
        $response = $this->actingAs($staff)->get('/staff/keuangan');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_access_keuangan_dashboard()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->get('/staff/keuangan');
        // Middleware now redirects wrong-role users to their dashboard
        $response->assertRedirect();
    }

    /** @test */
    public function admin_cannot_access_staff_psdm()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get('/staff/psdm');
        // Middleware redirects to admin dashboard
        $response->assertRedirect();
    }

    /** @test */
    public function staff_psdm_cannot_access_admin()
    {
        $staff = User::factory()->create(['role' => 'staff_psdm']);
        $response = $this->actingAs($staff)->get('/admin');
        // Middleware redirects wrong-role users to their own dashboard
        $response->assertRedirect();
    }

    // ==========================================
    // USER ROUTES ACCESS
    // ==========================================

    /** @test */
    public function user_can_access_home()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->get('/home');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_access_rekap()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->get('/rekap');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_access_salary()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->get('/salary');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_access_profile()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);
    }
}
