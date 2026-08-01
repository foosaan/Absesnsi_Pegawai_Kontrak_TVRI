<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        $this->shift = Shift::create([
            'name' => 'Normal',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'type' => 'normal',
            'tolerance_minutes' => 30,
        ]);
    }

    // ==========================================
    // TEST: canCheckOut()
    // ==========================================

    /** @test */
    public function can_checkout_when_min_checkout_time_is_null()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->shift->id,
            'check_in_time' => Carbon::today()->setTime(7, 0),
            'photo_path' => 'test.jpg',
            'latitude' => -7.79,
            'longitude' => 110.36,
            'status' => 'present',
            'min_check_out_time' => null,
        ]);

        $this->assertTrue($attendance->canCheckOut());
    }

    /** @test */
    public function can_checkout_when_current_time_is_after_min_checkout()
    {
        // Set waktu sekarang ke 15:30 (setelah min checkout 15:00)
        Carbon::setTestNow(Carbon::today()->setTime(15, 30));

        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->shift->id,
            'check_in_time' => Carbon::today()->setTime(7, 0),
            'photo_path' => 'test.jpg',
            'latitude' => -7.79,
            'longitude' => 110.36,
            'status' => 'present',
            'min_check_out_time' => Carbon::today()->setTime(15, 0),
        ]);

        $this->assertTrue($attendance->canCheckOut());
    }

    /** @test */
    public function cannot_checkout_when_current_time_is_before_min_checkout()
    {
        // Set waktu sekarang ke 12:00 (sebelum min checkout 15:00)
        Carbon::setTestNow(Carbon::today()->setTime(12, 0));

        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->shift->id,
            'check_in_time' => Carbon::today()->setTime(7, 0),
            'photo_path' => 'test.jpg',
            'latitude' => -7.79,
            'longitude' => 110.36,
            'status' => 'present',
            'min_check_out_time' => Carbon::today()->setTime(15, 0),
        ]);

        $this->assertFalse($attendance->canCheckOut());
    }

    // ==========================================
    // TEST: getRemainingMinutes()
    // ==========================================

    /** @test */
    public function remaining_minutes_is_zero_when_min_checkout_is_null()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->shift->id,
            'check_in_time' => Carbon::today()->setTime(7, 0),
            'photo_path' => 'test.jpg',
            'latitude' => -7.79,
            'longitude' => 110.36,
            'status' => 'present',
            'min_check_out_time' => null,
        ]);

        $this->assertEquals(0, $attendance->getRemainingMinutes());
    }

    /** @test */
    public function remaining_minutes_is_zero_when_can_checkout()
    {
        // Set waktu sekarang ke 16:00 (setelah min checkout 15:00)
        Carbon::setTestNow(Carbon::today()->setTime(16, 0));

        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->shift->id,
            'check_in_time' => Carbon::today()->setTime(7, 0),
            'photo_path' => 'test.jpg',
            'latitude' => -7.79,
            'longitude' => 110.36,
            'status' => 'present',
            'min_check_out_time' => Carbon::today()->setTime(15, 0),
        ]);

        $this->assertEquals(0, $attendance->getRemainingMinutes());
    }

    /** @test */
    public function remaining_minutes_returns_correct_value_when_cannot_checkout()
    {
        // Set waktu sekarang ke 13:00 (2 jam = 120 menit sebelum min checkout 15:00)
        Carbon::setTestNow(Carbon::today()->setTime(13, 0));

        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->shift->id,
            'check_in_time' => Carbon::today()->setTime(7, 0),
            'photo_path' => 'test.jpg',
            'latitude' => -7.79,
            'longitude' => 110.36,
            'status' => 'present',
            'min_check_out_time' => Carbon::today()->setTime(15, 0),
        ]);

        $remaining = $attendance->getRemainingMinutes();

        $this->assertEquals(120, $remaining);
    }

    /** @test */
    public function remaining_minutes_returns_correct_value_close_to_min_checkout()
    {
        // Set waktu sekarang ke 14:45 (15 menit sebelum min checkout 15:00)
        Carbon::setTestNow(Carbon::today()->setTime(14, 45));

        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->shift->id,
            'check_in_time' => Carbon::today()->setTime(7, 0),
            'photo_path' => 'test.jpg',
            'latitude' => -7.79,
            'longitude' => 110.36,
            'status' => 'present',
            'min_check_out_time' => Carbon::today()->setTime(15, 0),
        ]);

        $remaining = $attendance->getRemainingMinutes();

        $this->assertEquals(15, $remaining);
    }

    // ==========================================
    // TEST: Relasi
    // ==========================================

    /** @test */
    public function attendance_belongs_to_user()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->shift->id,
            'check_in_time' => Carbon::today()->setTime(7, 0),
            'photo_path' => 'test.jpg',
            'latitude' => -7.79,
            'longitude' => 110.36,
            'status' => 'present',
        ]);

        $this->assertInstanceOf(User::class, $attendance->user);
        $this->assertEquals($this->user->id, $attendance->user->id);
    }

    /** @test */
    public function attendance_belongs_to_shift()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->shift->id,
            'check_in_time' => Carbon::today()->setTime(7, 0),
            'photo_path' => 'test.jpg',
            'latitude' => -7.79,
            'longitude' => 110.36,
            'status' => 'present',
        ]);

        $this->assertInstanceOf(Shift::class, $attendance->shift);
        $this->assertEquals($this->shift->id, $attendance->shift->id);
    }
}
