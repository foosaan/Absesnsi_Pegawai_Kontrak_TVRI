<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    // ==================== STAFF CRUD ====================

    public function test_admin_can_view_staff_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.staffs'));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_create_staff_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.staffs.create'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_staff(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.staffs.store'), [
            'name' => 'Staff Baru',
            'nip' => '123456789012',
            'email' => 'staffbaru@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff_psdm',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'Staff Baru',
            'nip' => '123456789012',
            'email' => 'staffbaru@test.com',
            'role' => 'staff_psdm',
        ]);
    }

    public function test_admin_can_view_edit_staff_form(): void
    {
        $staff = User::factory()->create(['role' => 'staff_psdm']);

        $response = $this->actingAs($this->admin)->get(route('admin.staffs.edit', $staff));

        $response->assertStatus(200);
    }

    public function test_admin_can_update_staff(): void
    {
        $staff = User::factory()->create(['role' => 'staff_psdm']);

        $response = $this->actingAs($this->admin)->put(route('admin.staffs.update', $staff), [
            'name' => 'Staff Updated',
            'nip' => '999888777666',
            'email' => $staff->email,
            'role' => 'staff_psdm',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'name' => 'Staff Updated',
            'nip' => '999888777666',
        ]);
    }

    public function test_admin_can_delete_staff(): void
    {
        $staff = User::factory()->create(['role' => 'staff_psdm']);

        $response = $this->actingAs($this->admin)->delete(route('admin.staffs.delete', $staff));

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_create_staff_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.staffs.store'), [
            'name' => '',
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff_psdm',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_create_staff_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@test.com']);

        $response = $this->actingAs($this->admin)->post(route('admin.staffs.store'), [
            'name' => 'Test Staff',
            'email' => 'existing@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff_psdm',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // ==================== ADMIN CRUD ====================

    public function test_admin_can_view_admin_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.admins'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_new_admin(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.admins.store'), [
            'name' => 'Admin Baru',
            'nip' => '555666777888',
            'email' => 'adminbaru@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'Admin Baru',
            'nip' => '555666777888',
            'email' => 'adminbaru@test.com',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_update_another_admin(): void
    {
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($this->admin)->put(route('admin.admins.update', $otherAdmin), [
            'name' => 'Admin Updated',
            'nip' => '111222333444',
            'email' => $otherAdmin->email,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $otherAdmin->id,
            'name' => 'Admin Updated',
            'nip' => '111222333444',
        ]);
    }

    public function test_admin_can_delete_another_admin(): void
    {
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($this->admin)->delete(route('admin.admins.delete', $otherAdmin));

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $otherAdmin->id]);
    }

    // ==================== ACCESS CONTROL ====================

    public function test_guest_cannot_access_admin_panel(): void
    {
        $response = $this->get(route('admin.staffs'));

        $response->assertRedirect();
    }

    public function test_regular_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.staffs'));

        $this->assertNotEquals(200, $response->status());
    }

    public function test_create_admin_requires_unique_nip(): void
    {
        User::factory()->create(['nip' => '111111111']);

        $response = $this->actingAs($this->admin)->post(route('admin.admins.store'), [
            'name' => 'Test Admin',
            'nip' => '111111111',
            'email' => 'unique@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('nip');
    }
}
