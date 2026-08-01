<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\EmployeeProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // TEST: isAdmin()
    // ==========================================

    /** @test */
    public function user_with_admin_role_is_admin()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($user->isAdmin());
    }

    /** @test */
    public function user_with_non_admin_role_is_not_admin()
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->assertFalse($user->isAdmin());
    }

    // ==========================================
    // TEST: isShiftAttendance()
    // ==========================================

    /** @test */
    public function user_with_shift_attendance_type_is_shift()
    {
        // Factory otomatis membuat profile dengan attendance_type='normal',
        // jadi kita update profile yang sudah dibuat factory
        $user = User::factory()->create(['role' => 'user']);
        $user->profile->update(['attendance_type' => 'shift']);
        $user->load('profile');

        $this->assertTrue($user->isShiftAttendance());
    }

    /** @test */
    public function user_without_shift_attendance_type_is_not_shift()
    {
        // Factory otomatis membuat profile dengan attendance_type='normal'
        $user = User::factory()->create(['role' => 'user']);

        $this->assertFalse($user->isShiftAttendance());
    }

    // ==========================================
    // TEST: isNormalAttendance()
    // ==========================================

    /** @test */
    public function user_with_normal_attendance_type_is_normal()
    {
        // Factory otomatis membuat profile dengan attendance_type='normal'
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($user->isNormalAttendance());
    }

    /** @test */
    public function user_with_null_attendance_type_defaults_to_normal()
    {
        // Buat user tanpa auto-profile (admin tidak mendapat profile dari factory)
        $user = User::factory()->create(['role' => 'admin']);
        // Admin tidak punya profile, jadi attendance_type = null → isNormalAttendance = true
        $this->assertTrue($user->isNormalAttendance());
    }

    /** @test */
    public function user_without_profile_defaults_to_normal()
    {
        // Admin tidak memiliki EmployeeProfile (factory tidak membuatnya)
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertNull($user->profile);
        $this->assertTrue($user->isNormalAttendance());
    }

    // ==========================================
    // TEST: isUmumAttendance()
    // ==========================================

    /** @test */
    public function user_with_umum_attendance_type_is_umum()
    {
        $user = User::factory()->create(['role' => 'user']);
        $user->profile->update(['attendance_type' => 'umum']);
        $user->load('profile');

        $this->assertTrue($user->isUmumAttendance());
    }

    /** @test */
    public function user_with_normal_attendance_type_is_not_umum()
    {
        // Factory default = normal
        $user = User::factory()->create(['role' => 'user']);

        $this->assertFalse($user->isUmumAttendance());
    }

    // ==========================================
    // TEST: hasTwoFactorEnabled()
    // ==========================================

    /** @test */
    public function user_with_2fa_enabled_and_secret_has_two_factor()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'two_factor_enabled' => true,
            'two_factor_secret' => 'some-secret-key',
        ]);

        $this->assertTrue($user->hasTwoFactorEnabled());
    }

    /** @test */
    public function user_with_2fa_enabled_but_no_secret_does_not_have_two_factor()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'two_factor_enabled' => true,
            'two_factor_secret' => null,
        ]);

        $this->assertFalse($user->hasTwoFactorEnabled());
    }

    /** @test */
    public function user_with_2fa_disabled_does_not_have_two_factor()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'two_factor_enabled' => false,
            'two_factor_secret' => 'some-secret-key',
        ]);

        $this->assertFalse($user->hasTwoFactorEnabled());
    }

    // ==========================================
    // TEST: requiresTwoFactor()
    // ==========================================

    /** @test */
    public function admin_requires_two_factor()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($user->requiresTwoFactor());
    }

    /** @test */
    public function non_admin_does_not_require_two_factor()
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->assertFalse($user->requiresTwoFactor());
    }

    /** @test */
    public function staff_psdm_does_not_require_two_factor()
    {
        $user = User::factory()->create(['role' => 'staff_psdm']);

        $this->assertFalse($user->requiresTwoFactor());
    }

    // ==========================================
    // TEST: getProfilePhotoUrl()
    // ==========================================

    /** @test */
    public function user_without_photo_gets_default_avatar_url()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'profile_photo' => null,
        ]);

        $url = $user->getProfilePhotoUrl();

        $this->assertStringContainsString('ui-avatars.com', $url);
        $this->assertStringContainsString('JD', $url);
    }

    /** @test */
    public function user_with_photo_gets_storage_url()
    {
        $user = User::factory()->create([
            'profile_photo' => 'photos/user1.jpg',
        ]);

        $url = $user->getProfilePhotoUrl();

        $this->assertStringContainsString('storage/photos/user1.jpg', $url);
    }

    // ==========================================
    // TEST: Accessor Delegasi ke EmployeeProfile
    // ==========================================

    /** @test */
    public function user_accessor_returns_null_when_no_profile()
    {
        // Admin tidak memiliki EmployeeProfile (factory tidak membuatnya)
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertNull($user->alamat);
        $this->assertNull($user->no_telepon);
        $this->assertNull($user->jenis_kelamin);
        $this->assertNull($user->attendance_type);
    }

    /** @test */
    public function user_accessor_delegates_to_profile()
    {
        $user = User::factory()->create(['role' => 'user']);

        // Update profile yang sudah dibuat oleh factory
        $user->profile->update([
            'nik' => '1234567890123456',
            'alamat' => 'Jl. Sudirman No. 1',
            'no_telepon' => '081234567890',
            'jenis_kelamin' => 'L',
            'attendance_type' => 'normal',
        ]);

        $user->load('profile');

        $this->assertEquals('1234567890123456', $user->nik);
        $this->assertEquals('Jl. Sudirman No. 1', $user->alamat);
        $this->assertEquals('081234567890', $user->no_telepon);
        $this->assertEquals('L', $user->jenis_kelamin);
        $this->assertEquals('normal', $user->attendance_type);
    }

    // ==========================================
    // TEST: isSatpam() & isOB()
    // ==========================================

    /** @test */
    public function user_is_not_satpam_without_jabatan()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertFalse($user->isSatpam());
    }

    /** @test */
    public function user_is_not_ob_without_jabatan()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertFalse($user->isOB());
    }

    // ==========================================
    // TEST: Relasi
    // ==========================================

    /** @test */
    public function user_has_one_profile()
    {
        $user = User::factory()->create(['role' => 'user']);

        // Factory otomatis membuat profile
        $this->assertInstanceOf(EmployeeProfile::class, $user->profile);
        $this->assertEquals($user->id, $user->profile->user_id);
    }

    /** @test */
    public function admin_has_no_profile()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertNull($user->profile);
    }
}
