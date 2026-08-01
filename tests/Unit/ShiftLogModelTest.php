<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ShiftLog;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShiftLogModelTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // TEST: getFieldLabelAttribute()
    // ==========================================

    /** @test */
    public function field_label_returns_jam_mulai_for_start_time()
    {
        $log = new ShiftLog(['field_name' => 'start_time']);

        $this->assertEquals('Jam Mulai', $log->field_label);
    }

    /** @test */
    public function field_label_returns_jam_selesai_for_end_time()
    {
        $log = new ShiftLog(['field_name' => 'end_time']);

        $this->assertEquals('Jam Selesai', $log->field_label);
    }

    /** @test */
    public function field_label_returns_toleransi_for_tolerance_minutes()
    {
        $log = new ShiftLog(['field_name' => 'tolerance_minutes']);

        $this->assertEquals('Toleransi (menit)', $log->field_label);
    }

    /** @test */
    public function field_label_returns_raw_value_for_unknown_field()
    {
        $log = new ShiftLog(['field_name' => 'name']);

        $this->assertEquals('name', $log->field_label);
    }

    // ==========================================
    // TEST: Relasi
    // ==========================================

    /** @test */
    public function shift_log_belongs_to_shift()
    {
        $shift = Shift::create([
            'name' => 'Normal',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'type' => 'normal',
            'tolerance_minutes' => 30,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $log = ShiftLog::create([
            'shift_id' => $shift->id,
            'changed_by' => $admin->id,
            'field_name' => 'start_time',
            'old_value' => '07:00:00',
            'new_value' => '08:00:00',
        ]);

        $this->assertInstanceOf(Shift::class, $log->shift);
        $this->assertEquals($shift->id, $log->shift->id);
    }

    /** @test */
    public function shift_log_belongs_to_changed_by_user()
    {
        $shift = Shift::create([
            'name' => 'Normal',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'type' => 'normal',
            'tolerance_minutes' => 30,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $log = ShiftLog::create([
            'shift_id' => $shift->id,
            'changed_by' => $admin->id,
            'field_name' => 'start_time',
            'old_value' => '07:00:00',
            'new_value' => '08:00:00',
        ]);

        $this->assertInstanceOf(User::class, $log->changedByUser);
        $this->assertEquals($admin->id, $log->changedByUser->id);
    }
}
