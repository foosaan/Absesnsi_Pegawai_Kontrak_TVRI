<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SalaryService;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Salary;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SalaryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SalaryService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SalaryService();
        $this->user = User::factory()->create(['role' => 'user']);
    }

    // ==========================================
    // TEST: getWorkingDaysInMonth()
    // ==========================================

    /** @test */
    public function working_days_in_july_2026_is_correct()
    {
        // Juli 2026: 1 Jul (Rabu) - 31 Jul (Jumat)
        // Hitung hari kerja Senin-Jumat
        $workDays = $this->service->getWorkingDaysInMonth(2026, 7);

        $this->assertEquals(23, $workDays);
    }

    /** @test */
    public function working_days_in_february_2026_is_correct()
    {
        // Februari 2026: 28 hari (bukan kabisat)
        $workDays = $this->service->getWorkingDaysInMonth(2026, 2);

        $this->assertEquals(20, $workDays);
    }

    /** @test */
    public function working_days_in_february_2028_leap_year()
    {
        // Februari 2028: 29 hari (kabisat)
        $workDays = $this->service->getWorkingDaysInMonth(2028, 2);

        $this->assertEquals(21, $workDays);
    }

    /** @test */
    public function working_days_in_january_2026_is_correct()
    {
        // Januari 2026
        $workDays = $this->service->getWorkingDaysInMonth(2026, 1);

        $this->assertEquals(22, $workDays);
    }

    // ==========================================
    // TEST: calculateSalary()
    // ==========================================

    /** @test */
    public function calculate_salary_returns_correct_structure()
    {
        $result = $this->service->calculateSalary($this->user, 7, 2026, 5000000);

        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('month', $result);
        $this->assertArrayHasKey('year', $result);
        $this->assertArrayHasKey('base_salary', $result);
        $this->assertArrayHasKey('total_work_days', $result);
        $this->assertArrayHasKey('days_present', $result);
        $this->assertArrayHasKey('total_late_days', $result);
        $this->assertArrayHasKey('total_absent_days', $result);
        $this->assertArrayHasKey('final_salary', $result);

        $this->assertEquals($this->user->id, $result['user_id']);
        $this->assertEquals(7, $result['month']);
        $this->assertEquals(2026, $result['year']);
        $this->assertEquals(5000000, $result['base_salary']);
    }

    /** @test */
    public function calculate_salary_with_no_attendance_returns_all_absent()
    {
        $result = $this->service->calculateSalary($this->user, 7, 2026, 5000000);

        $expectedWorkDays = $this->service->getWorkingDaysInMonth(2026, 7);

        $this->assertEquals(0, $result['days_present']);
        $this->assertEquals($expectedWorkDays, $result['total_absent_days']);
        $this->assertEquals(0, $result['total_late_days']);
    }

    /** @test */
    public function calculate_salary_counts_present_and_late_days()
    {
        $shift = Shift::create([
            'name' => 'Normal',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'type' => 'normal',
            'tolerance_minutes' => 30,
        ]);

        // Buat 5 attendance (3 present, 2 late) di bulan Juli 2026
        for ($i = 1; $i <= 3; $i++) {
            Attendance::create([
                'user_id' => $this->user->id,
                'shift_id' => $shift->id,
                'check_in_time' => Carbon::create(2026, 7, $i, 7, 0),
                'photo_path' => 'test.jpg',
                'latitude' => -7.79,
                'longitude' => 110.36,
                'status' => 'present',
                'work_date' => Carbon::create(2026, 7, $i)->toDateString(),
            ]);
        }

        for ($i = 4; $i <= 5; $i++) {
            Attendance::create([
                'user_id' => $this->user->id,
                'shift_id' => $shift->id,
                'check_in_time' => Carbon::create(2026, 7, $i + 2, 7, 35),
                'photo_path' => 'test.jpg',
                'latitude' => -7.79,
                'longitude' => 110.36,
                'status' => 'late',
                'work_date' => Carbon::create(2026, 7, $i + 2)->toDateString(),
            ]);
        }

        $result = $this->service->calculateSalary($this->user, 7, 2026, 5000000);

        $this->assertEquals(5, $result['days_present']);
        $this->assertEquals(2, $result['total_late_days']);
    }

    /** @test */
    public function calculate_salary_final_salary_equals_base_salary()
    {
        // Tidak ada denda otomatis, jadi final_salary = base_salary
        $result = $this->service->calculateSalary($this->user, 7, 2026, 6000000);

        $this->assertEquals(6000000, $result['final_salary']);
    }

    // ==========================================
    // TEST: saveSalary()
    // ==========================================

    /** @test */
    public function save_salary_creates_new_record_with_draft_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $salary = $this->service->saveSalary([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5000000,
            'final_salary' => 5000000,
        ], $admin->id);

        $this->assertInstanceOf(Salary::class, $salary);
        $this->assertEquals('draft', $salary->status);
        $this->assertEquals($this->user->id, $salary->user_id);
        $this->assertEquals(7, $salary->month);
        $this->assertEquals(2026, $salary->year);
    }

    /** @test */
    public function save_salary_updates_existing_record_without_changing_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Buat salary awal
        $original = Salary::create([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5000000,
            'final_salary' => 5000000,
            'status' => 'signed', // Status sudah signed
            'created_by' => $admin->id,
        ]);

        // Update menggunakan saveSalary
        $updated = $this->service->saveSalary([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5500000,
            'final_salary' => 5500000,
        ], $admin->id);

        // Status harus tetap 'signed', tidak direset ke 'draft'
        $this->assertEquals($original->id, $updated->id);
        $this->assertEquals(5500000, $updated->base_salary);
        $this->assertEquals('signed', $updated->status);
    }

    /** @test */
    public function save_salary_does_not_duplicate_records_for_same_period()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->service->saveSalary([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5000000,
            'final_salary' => 5000000,
        ], $admin->id);

        $this->service->saveSalary([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5500000,
            'final_salary' => 5500000,
        ], $admin->id);

        // Hanya boleh 1 record untuk user+bulan+tahun yang sama
        $count = Salary::where('user_id', $this->user->id)
            ->where('month', 7)
            ->where('year', 2026)
            ->count();

        $this->assertEquals(1, $count);
    }
}
