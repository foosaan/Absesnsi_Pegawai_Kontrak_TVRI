<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Salary;
use App\Models\User;
use App\Models\SalaryDeduction;
use App\Models\DeductionType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SalaryModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'user']);
    }

    // ==========================================
    // TEST: isSigned()
    // ==========================================

    /** @test */
    public function salary_is_signed_when_has_signer_and_timestamp()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $salary = Salary::create([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5000000,
            'final_salary' => 5000000,
            'signed_by' => $admin->id,
            'signed_at' => Carbon::now(),
        ]);

        $this->assertTrue($salary->isSigned());
    }

    /** @test */
    public function salary_is_not_signed_when_missing_signer()
    {
        $salary = Salary::create([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5000000,
            'final_salary' => 5000000,
            'signed_by' => null,
            'signed_at' => null,
        ]);

        $this->assertFalse($salary->isSigned());
    }

    /** @test */
    public function salary_is_not_signed_when_missing_timestamp()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $salary = Salary::create([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5000000,
            'final_salary' => 5000000,
            'signed_by' => $admin->id,
            'signed_at' => null,
        ]);

        $this->assertFalse($salary->isSigned());
    }

    // ==========================================
    // TEST: isDraft()
    // ==========================================

    /** @test */
    public function salary_is_draft_when_not_signed()
    {
        $salary = Salary::create([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5000000,
            'final_salary' => 5000000,
        ]);

        $this->assertTrue($salary->isDraft());
    }

    /** @test */
    public function salary_is_not_draft_when_signed()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $salary = Salary::create([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5000000,
            'final_salary' => 5000000,
            'signed_by' => $admin->id,
            'signed_at' => Carbon::now(),
        ]);

        $this->assertFalse($salary->isDraft());
    }

    // ==========================================
    // TEST: getMonthNameAttribute()
    // ==========================================

    /** @test */
    public function month_name_returns_correct_indonesian_name()
    {
        $testCases = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        foreach ($testCases as $monthNumber => $expectedName) {
            $salary = Salary::create([
                'user_id' => $this->user->id,
                'month' => $monthNumber,
                'year' => 2026,
                'base_salary' => 5000000,
                'final_salary' => 5000000,
            ]);

            $this->assertEquals($expectedName, $salary->month_name, "Bulan {$monthNumber} harus '{$expectedName}'");

            // Hapus record untuk iterasi berikutnya
            $salary->delete();
        }
    }

    /** @test */
    public function month_name_returns_empty_for_invalid_month()
    {
        $salary = Salary::create([
            'user_id' => $this->user->id,
            'month' => 13,
            'year' => 2026,
            'base_salary' => 5000000,
            'final_salary' => 5000000,
        ]);

        $this->assertEquals('', $salary->month_name);
    }

    // ==========================================
    // TEST: getPeriodAttribute()
    // ==========================================

    /** @test */
    public function period_returns_month_name_and_year()
    {
        $salary = Salary::create([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5000000,
            'final_salary' => 5000000,
        ]);

        $this->assertEquals('Juli 2026', $salary->period);
    }

    /** @test */
    public function period_returns_correct_for_january()
    {
        $salary = Salary::create([
            'user_id' => $this->user->id,
            'month' => 1,
            'year' => 2027,
            'base_salary' => 5000000,
            'final_salary' => 5000000,
        ]);

        $this->assertEquals('Januari 2027', $salary->period);
    }

    // ==========================================
    // TEST: getStatusLabelAttribute()
    // ==========================================

    /** @test */
    public function status_label_returns_draft_when_not_signed()
    {
        $salary = Salary::create([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5000000,
            'final_salary' => 5000000,
        ]);

        $this->assertEquals('Draft', $salary->status_label);
    }

    /** @test */
    public function status_label_returns_ditandatangani_when_signed()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $salary = Salary::create([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5000000,
            'final_salary' => 5000000,
            'signed_by' => $admin->id,
            'signed_at' => Carbon::now(),
        ]);

        $this->assertEquals('Ditandatangani', $salary->status_label);
    }

    // ==========================================
    // TEST: Relasi
    // ==========================================

    /** @test */
    public function salary_belongs_to_user()
    {
        $salary = Salary::create([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5000000,
            'final_salary' => 5000000,
        ]);

        $this->assertInstanceOf(User::class, $salary->user);
        $this->assertEquals($this->user->id, $salary->user->id);
    }

    /** @test */
    public function salary_has_many_deductions()
    {
        $salary = Salary::create([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5000000,
            'final_salary' => 4500000,
        ]);

        $deductionType = DeductionType::create([
            'name' => 'BPJS',
            'description' => 'BPJS Kesehatan',
            'is_active' => true,
        ]);

        SalaryDeduction::create([
            'salary_id' => $salary->id,
            'deduction_type_id' => $deductionType->id,
            'amount' => 250000,
        ]);

        SalaryDeduction::create([
            'salary_id' => $salary->id,
            'deduction_type_id' => $deductionType->id,
            'amount' => 250000,
        ]);

        $salary->load('salaryDeductions');

        $this->assertCount(2, $salary->salaryDeductions);
    }

    /** @test */
    public function salary_belongs_to_signer()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $salary = Salary::create([
            'user_id' => $this->user->id,
            'month' => 7,
            'year' => 2026,
            'base_salary' => 5000000,
            'final_salary' => 5000000,
            'signed_by' => $admin->id,
            'signed_at' => Carbon::now(),
        ]);

        $this->assertInstanceOf(User::class, $salary->signer);
        $this->assertEquals($admin->id, $salary->signer->id);
    }
}
