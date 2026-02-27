<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Salary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class SalaryImportTest extends TestCase
{
    use RefreshDatabase;

    private User $keuanganStaff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keuanganStaff = User::factory()->create(['role' => 'staff_keuangan']);
    }

    public function test_keuangan_staff_can_view_import_form(): void
    {
        $response = $this->actingAs($this->keuanganStaff)
            ->get(route('staff.keuangan.salaries.import.form'));

        $response->assertStatus(200);
    }

    public function test_import_requires_file(): void
    {
        $response = $this->actingAs($this->keuanganStaff)
            ->post(route('staff.keuangan.salaries.import'), [
                'month' => 1,
                'year' => 2026,
            ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_requires_month(): void
    {
        $file = UploadedFile::fake()->create('gaji.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($this->keuanganStaff)
            ->post(route('staff.keuangan.salaries.import'), [
                'file' => $file,
                'year' => 2026,
            ]);

        $response->assertSessionHasErrors('month');
    }

    public function test_import_requires_year(): void
    {
        $file = UploadedFile::fake()->create('gaji.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($this->keuanganStaff)
            ->post(route('staff.keuangan.salaries.import'), [
                'file' => $file,
                'month' => 1,
            ]);

        $response->assertSessionHasErrors('year');
    }

    public function test_import_rejects_non_excel_file(): void
    {
        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $response = $this->actingAs($this->keuanganStaff)
            ->post(route('staff.keuangan.salaries.import'), [
                'file' => $file,
                'month' => 1,
                'year' => 2026,
            ]);

        $response->assertSessionHas('error');
    }

    public function test_import_month_must_be_between_1_and_12(): void
    {
        $file = UploadedFile::fake()->create('gaji.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($this->keuanganStaff)
            ->post(route('staff.keuangan.salaries.import'), [
                'file' => $file,
                'month' => 13,
                'year' => 2026,
            ]);

        $response->assertSessionHasErrors('month');
    }

    public function test_guest_cannot_access_import_page(): void
    {
        $response = $this->get(route('staff.keuangan.salaries.import.form'));

        $response->assertRedirect();
    }

    public function test_regular_user_cannot_access_import_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->get(route('staff.keuangan.salaries.import.form'));

        // Should be forbidden or redirected
        $this->assertNotEquals(200, $response->status());
    }
}
