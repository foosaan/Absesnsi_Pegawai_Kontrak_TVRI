<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AttendanceService;
use App\Models\User;
use App\Models\Shift;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\BusinessTrip;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceService $service;
    protected User $user;
    protected Shift $normalShift;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AttendanceService();

        // Buat user test
        $this->user = User::factory()->create([
            'role' => 'user',
        ]);

        // Buat shift normal (07:00 - 15:00)
        $this->normalShift = Shift::create([
            'name' => 'Normal',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'type' => 'normal',
            'tolerance_minutes' => 30,
        ]);
    }

    // ==========================================
    // TEST: canCheckIn()
    // ==========================================

    /** @test */
    public function user_can_check_in_during_valid_time()
    {
        // Set waktu ke jam 07:00 (tepat waktu)
        Carbon::setTestNow(Carbon::today()->setTime(7, 0));

        $result = $this->service->canCheckIn($this->user);

        $this->assertTrue($result['can']);
        $this->assertNotNull($result['shift']);
        $this->assertEquals('Silakan lakukan Absen Masuk.', $result['message']);
    }

    /** @test */
    public function user_can_check_in_1_hour_before_shift()
    {
        // Set waktu ke jam 06:00 (1 jam sebelum shift)
        Carbon::setTestNow(Carbon::today()->setTime(6, 0));

        $result = $this->service->canCheckIn($this->user);

        $this->assertTrue($result['can']);
    }

    /** @test */
    public function user_cannot_check_in_too_early()
    {
        // Set waktu ke jam 05:30 (terlalu awal, sebelum toleransi 1 jam)
        Carbon::setTestNow(Carbon::today()->setTime(5, 30));

        $result = $this->service->canCheckIn($this->user);

        $this->assertFalse($result['can']);
        $this->assertStringContainsString('belum dimulai', $result['message']);
    }

    /** @test */
    public function user_cannot_check_in_twice_same_day()
    {
        Carbon::setTestNow(Carbon::today()->setTime(7, 0));

        // Buat attendance yang sudah ada
        Attendance::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->normalShift->id,
            'check_in_time' => Carbon::today()->setTime(7, 0),
            'photo_path' => 'test.jpg',
            'latitude' => -7.79,
            'longitude' => 110.36,
            'status' => 'present',
            'min_check_out_time' => Carbon::today()->setTime(15, 0),
            'max_check_out_time' => Carbon::today()->setTime(18, 0),
        ]);

        $result = $this->service->canCheckIn($this->user);

        $this->assertFalse($result['can']);
        $this->assertStringContainsString('belum checkout', $result['message']);
    }

    /** @test */
    public function user_cannot_check_in_when_on_leave()
    {
        Carbon::setTestNow(Carbon::today()->setTime(7, 0));

        Leave::create([
            'user_id' => $this->user->id,
            'type' => 'cuti_tahunan',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(3),
            'reason' => 'Liburan',
            'status' => 'approved',
        ]);

        $result = $this->service->canCheckIn($this->user);

        $this->assertFalse($result['can']);
        $this->assertStringContainsString('cuti', $result['message']);
    }

    /** @test */
    public function user_cannot_check_in_when_on_business_trip()
    {
        Carbon::setTestNow(Carbon::today()->setTime(7, 0));

        BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Jakarta',
            'purpose' => 'Rapat',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(2),
            'status' => 'approved',
        ]);

        $result = $this->service->canCheckIn($this->user);

        $this->assertFalse($result['can']);
        $this->assertStringContainsString('dinas', $result['message']);
    }

    /** @test */
    public function user_is_marked_late_when_check_in_after_tolerance()
    {
        // Set waktu ke 07:35 (lewat toleransi 30 menit)
        Carbon::setTestNow(Carbon::today()->setTime(7, 35));

        $result = $this->service->canCheckIn($this->user);

        $this->assertTrue($result['can']);
        $this->assertTrue($result['is_late']);
        $this->assertStringContainsString('terlambat', $result['message']);
    }

    // ==========================================
    // TEST: isLate()
    // ==========================================

    /** @test */
    public function user_is_not_late_within_tolerance()
    {
        // 07:29 masih dalam toleransi 30 menit (07:00 + 30 = 07:30)
        $checkInTime = Carbon::today()->setTime(7, 29);
        $result = $this->service->isLate($this->normalShift, $checkInTime);

        $this->assertFalse($result);
    }

    /** @test */
    public function user_is_late_after_tolerance()
    {
        // 07:35 sudah lewat toleransi 30 menit
        $checkInTime = Carbon::today()->setTime(7, 35);
        $result = $this->service->isLate($this->normalShift, $checkInTime);

        $this->assertTrue($result);
    }

    // ==========================================
    // TEST: calculateMinCheckOutTime()
    // ==========================================

    /** @test */
    public function min_checkout_is_shift_end_when_check_in_early()
    {
        // Check-in jam 06:00, shift pulang 15:00
        // Min checkout harus 15:00 (bukan 14:00)
        $checkInTime = Carbon::today()->setTime(6, 0);
        $minCheckout = $this->service->calculateMinCheckOutTime($checkInTime, $this->normalShift, false);

        $this->assertEquals('15:00', $minCheckout->format('H:i'));
    }

    /** @test */
    public function min_checkout_is_checkin_plus_8h_when_after_shift_end()
    {
        // Check-in jam 07:20 → 07:20 + 8 = 15:20 > shift end 15:00
        // Min checkout harus 15:20
        $checkInTime = Carbon::today()->setTime(7, 20);
        $minCheckout = $this->service->calculateMinCheckOutTime($checkInTime, $this->normalShift, false);

        $this->assertEquals('15:20', $minCheckout->format('H:i'));
    }

    /** @test */
    public function min_checkout_equals_shift_end_when_on_time()
    {
        // Check-in jam 07:00 → 07:00 + 8 = 15:00 == shift end 15:00
        $checkInTime = Carbon::today()->setTime(7, 0);
        $minCheckout = $this->service->calculateMinCheckOutTime($checkInTime, $this->normalShift, false);

        $this->assertEquals('15:00', $minCheckout->format('H:i'));
    }

    /** @test */
    public function min_checkout_is_shift_end_when_late()
    {
        // Check-in jam 07:35 (terlambat), shift pulang 15:00
        // Min checkout harus 15:00 (ikut shift)
        $checkInTime = Carbon::today()->setTime(7, 35);
        $minCheckout = $this->service->calculateMinCheckOutTime($checkInTime, $this->normalShift, true);

        $this->assertEquals('15:00', $minCheckout->format('H:i'));
    }

    // ==========================================
    // TEST: canCheckOut()
    // ==========================================

    /** @test */
    public function user_cannot_checkout_before_min_time()
    {
        Carbon::setTestNow(Carbon::today()->setTime(12, 0));

        Attendance::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->normalShift->id,
            'check_in_time' => Carbon::today()->setTime(7, 0),
            'photo_path' => 'test.jpg',
            'latitude' => -7.79,
            'longitude' => 110.36,
            'status' => 'present',
            'min_check_out_time' => Carbon::today()->setTime(15, 0),
            'max_check_out_time' => Carbon::today()->setTime(18, 0),
        ]);

        $result = $this->service->canCheckOut($this->user);

        $this->assertFalse($result['can']);
        $this->assertStringContainsString('belum bisa', $result['message']);
    }

    /** @test */
    public function user_can_checkout_after_min_time()
    {
        Carbon::setTestNow(Carbon::today()->setTime(15, 5));

        Attendance::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->normalShift->id,
            'check_in_time' => Carbon::today()->setTime(7, 0),
            'photo_path' => 'test.jpg',
            'latitude' => -7.79,
            'longitude' => 110.36,
            'status' => 'present',
            'min_check_out_time' => Carbon::today()->setTime(15, 0),
            'max_check_out_time' => Carbon::today()->setTime(18, 0),
        ]);

        $result = $this->service->canCheckOut($this->user);

        $this->assertTrue($result['can']);
    }

    /** @test */
    public function user_cannot_checkout_without_checkin()
    {
        Carbon::setTestNow(Carbon::today()->setTime(15, 0));

        $result = $this->service->canCheckOut($this->user);

        $this->assertFalse($result['can']);
        $this->assertStringContainsString('belum absen masuk', $result['message']);
    }
}
