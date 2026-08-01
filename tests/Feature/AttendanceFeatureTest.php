<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Shift;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttendanceFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'user',
        ]);

        $this->shift = Shift::create([
            'name' => 'Normal',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'type' => 'normal',
            'tolerance_minutes' => 30,
        ]);
    }

    // ==========================================
    // CHECK-IN TESTS
    // ==========================================

    /** @test */
    public function user_can_access_dashboard()
    {
        $response = $this->actingAs($this->user)->get('/home');
        $response->assertStatus(200);
    }

    /** @test */
    public function guest_cannot_access_attendance()
    {
        $response = $this->get('/attendance');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function user_can_view_checkin_page()
    {
        Carbon::setTestNow(Carbon::today()->setTime(7, 0));
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_check_in_with_valid_data()
    {
        Carbon::setTestNow(Carbon::today()->setTime(7, 0));
        Storage::fake('public');

        $response = $this->actingAs($this->user)->post('/attendance/check-in', [
            'photo' => UploadedFile::fake()->image('selfie.jpg'),
            'latitude' => -7.766723,
            'longitude' => 110.377012,
            'accuracy' => 10,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'status' => 'present',
        ]);
    }

    /** @test */
    public function user_cannot_check_in_without_photo()
    {
        Carbon::setTestNow(Carbon::today()->setTime(7, 0));

        $response = $this->actingAs($this->user)->post('/attendance/check-in', [
            'latitude' => -7.7956,
            'longitude' => 110.3695,
        ]);

        $response->assertSessionHasErrors('photo');
    }

    /** @test */
    public function user_cannot_check_in_twice()
    {
        Carbon::setTestNow(Carbon::today()->setTime(7, 0));
        Storage::fake('public');

        // Check-in pertama
        Attendance::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->shift->id,
            'check_in_time' => Carbon::now(),
            'photo_path' => 'test.jpg',
            'latitude' => -7.79,
            'longitude' => 110.36,
            'status' => 'present',
            'min_check_out_time' => Carbon::today()->setTime(15, 0),
            'max_check_out_time' => Carbon::today()->setTime(18, 0),
        ]);

        // Check-in kedua harus gagal
        $response = $this->actingAs($this->user)->post('/attendance/check-in', [
            'photo' => UploadedFile::fake()->image('selfie2.jpg'),
            'latitude' => -7.766723,
            'longitude' => 110.377012,
            'accuracy' => 10,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ==========================================
    // CHECK-OUT TESTS
    // ==========================================

    /** @test */
    public function user_can_check_out_after_min_time()
    {
        Storage::fake('public');

        Attendance::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->shift->id,
            'check_in_time' => Carbon::today()->setTime(7, 0),
            'photo_path' => 'test.jpg',
            'latitude' => -7.79,
            'longitude' => 110.36,
            'status' => 'present',
            'min_check_out_time' => Carbon::today()->setTime(15, 0),
            'max_check_out_time' => Carbon::today()->setTime(18, 0),
        ]);

        Carbon::setTestNow(Carbon::today()->setTime(15, 5));

        $response = $this->actingAs($this->user)->post('/attendance/check-out', [
            'photo' => UploadedFile::fake()->image('checkout.jpg'),
            'latitude' => -7.766723,
            'longitude' => 110.377012,
            'accuracy' => 10,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    // ==========================================
    // REKAP TESTS
    // ==========================================

    /** @test */
    public function user_can_view_rekap()
    {
        $response = $this->actingAs($this->user)->get('/rekap');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_filter_rekap_by_month()
    {
        $response = $this->actingAs($this->user)->get('/rekap?month=2&year=2026');
        $response->assertStatus(200);
    }
}
