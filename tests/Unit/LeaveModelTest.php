<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LeaveModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'user']);
    }

    // ==========================================
    // TEST: getTypeLabelAttribute()
    // ==========================================

    /** @test */
    public function type_label_returns_cuti_tahunan()
    {
        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'cuti_tahunan',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(2),
            'reason' => 'Liburan',
            'status' => 'pending',
        ]);

        $this->assertEquals('Cuti Tahunan', $leave->type_label);
    }

    /** @test */
    public function type_label_returns_sakit()
    {
        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'sakit',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'reason' => 'Demam',
            'status' => 'pending',
        ]);

        $this->assertEquals('Sakit', $leave->type_label);
    }

    /** @test */
    public function type_label_returns_izin()
    {
        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'izin',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'reason' => 'Urusan pribadi',
            'status' => 'pending',
        ]);

        $this->assertEquals('Izin', $leave->type_label);
    }

    /** @test */
    public function type_label_returns_lainnya()
    {
        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'lainnya',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'reason' => 'Lain-lain',
            'status' => 'pending',
        ]);

        $this->assertEquals('Lainnya', $leave->type_label);
    }

    // ==========================================
    // TEST: getStatusLabelAttribute()
    // ==========================================

    /** @test */
    public function status_label_returns_menunggu_persetujuan_for_pending()
    {
        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'cuti_tahunan',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'reason' => 'Test',
            'status' => 'pending',
        ]);

        $this->assertEquals('Menunggu Persetujuan', $leave->status_label);
    }

    /** @test */
    public function status_label_returns_disetujui_for_approved()
    {
        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'cuti_tahunan',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'reason' => 'Test',
            'status' => 'approved',
        ]);

        $this->assertEquals('Disetujui', $leave->status_label);
    }

    /** @test */
    public function status_label_returns_ditolak_for_rejected()
    {
        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'cuti_tahunan',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'reason' => 'Test',
            'status' => 'rejected',
        ]);

        $this->assertEquals('Ditolak', $leave->status_label);
    }

    // ==========================================
    // TEST: getTotalDaysAttribute()
    // ==========================================

    /** @test */
    public function total_days_returns_1_for_single_day_leave()
    {
        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'sakit',
            'start_date' => Carbon::create(2026, 7, 10),
            'end_date' => Carbon::create(2026, 7, 10),
            'reason' => 'Sakit',
            'status' => 'pending',
        ]);

        $this->assertEquals(1, $leave->total_days);
    }

    /** @test */
    public function total_days_returns_correct_count_for_multi_day_leave()
    {
        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'cuti_tahunan',
            'start_date' => Carbon::create(2026, 7, 10),
            'end_date' => Carbon::create(2026, 7, 14),
            'reason' => 'Liburan',
            'status' => 'pending',
        ]);

        $this->assertEquals(5, $leave->total_days);
    }

    // ==========================================
    // TEST: Scopes
    // ==========================================

    /** @test */
    public function scope_pending_returns_only_pending_leaves()
    {
        Leave::create([
            'user_id' => $this->user->id,
            'type' => 'cuti_tahunan',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'reason' => 'A',
            'status' => 'pending',
        ]);

        Leave::create([
            'user_id' => $this->user->id,
            'type' => 'sakit',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(5),
            'reason' => 'B',
            'status' => 'approved',
        ]);

        Leave::create([
            'user_id' => $this->user->id,
            'type' => 'izin',
            'start_date' => Carbon::today()->addDays(10),
            'end_date' => Carbon::today()->addDays(10),
            'reason' => 'C',
            'status' => 'rejected',
        ]);

        $pendingLeaves = Leave::pending()->get();

        $this->assertCount(1, $pendingLeaves);
        $this->assertEquals('pending', $pendingLeaves->first()->status);
    }

    /** @test */
    public function scope_approved_returns_only_approved_leaves()
    {
        Leave::create([
            'user_id' => $this->user->id,
            'type' => 'cuti_tahunan',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'reason' => 'A',
            'status' => 'pending',
        ]);

        Leave::create([
            'user_id' => $this->user->id,
            'type' => 'sakit',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(5),
            'reason' => 'B',
            'status' => 'approved',
        ]);

        $approvedLeaves = Leave::approved()->get();

        $this->assertCount(1, $approvedLeaves);
        $this->assertEquals('approved', $approvedLeaves->first()->status);
    }

    // ==========================================
    // TEST: Relasi
    // ==========================================

    /** @test */
    public function leave_belongs_to_user()
    {
        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'cuti_tahunan',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'reason' => 'Test',
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(User::class, $leave->user);
        $this->assertEquals($this->user->id, $leave->user->id);
    }

    /** @test */
    public function leave_belongs_to_approver()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'cuti_tahunan',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'reason' => 'Test',
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        $this->assertInstanceOf(User::class, $leave->approver);
        $this->assertEquals($admin->id, $leave->approver->id);
    }
}
