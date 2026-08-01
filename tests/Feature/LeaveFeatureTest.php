<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LeaveFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $staffPsdm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'user',
        ]);

        $this->staffPsdm = User::factory()->create([
            'role' => 'staff_psdm',
        ]);
    }

    // ==========================================
    // USER: AJUKAN CUTI
    // ==========================================

    /** @test */
    public function user_can_view_leave_list()
    {
        $response = $this->actingAs($this->user)->get('/cuti');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_view_create_leave_form()
    {
        $response = $this->actingAs($this->user)->get('/cuti/create');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_submit_leave_request()
    {
        Storage::fake('public');

        $response = $this->actingAs($this->user)->post('/cuti', [
            'type' => 'sakit',
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'reason' => 'Demam tinggi',
            'attachment' => UploadedFile::fake()->image('surat-dokter.jpg'),
        ]);

        $response->assertRedirect(route('user.leaves'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('leaves', [
            'user_id' => $this->user->id,
            'type' => 'sakit',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function user_cannot_submit_leave_without_reason()
    {
        $response = $this->actingAs($this->user)->post('/cuti', [
            'type' => 'sakit',
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
        ]);

        $response->assertSessionHasErrors('reason');
    }

    /** @test */
    public function user_cannot_submit_overlapping_leave()
    {
        // Buat cuti pertama
        Leave::create([
            'user_id' => $this->user->id,
            'type' => 'sakit',
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(3),
            'reason' => 'Sakit',
            'status' => 'pending',
        ]);

        // Ajukan cuti yang overlap
        $response = $this->actingAs($this->user)->post('/cuti', [
            'type' => 'cuti_tahunan',
            'start_date' => Carbon::tomorrow()->addDay()->toDateString(),
            'end_date' => Carbon::tomorrow()->addDays(5)->toDateString(),
            'reason' => 'Liburan',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function user_can_cancel_pending_leave()
    {
        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'sakit',
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(2),
            'reason' => 'Sakit',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->delete("/cuti/{$leave->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('leaves', ['id' => $leave->id]);
    }

    /** @test */
    public function user_cannot_cancel_approved_leave()
    {
        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'sakit',
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(2),
            'reason' => 'Sakit',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->user)->delete("/cuti/{$leave->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('leaves', ['id' => $leave->id]);
    }

    /** @test */
    public function user_cannot_cancel_other_users_leave()
    {
        $otherUser = User::factory()->create(['role' => 'user']);
        $leave = Leave::create([
            'user_id' => $otherUser->id,
            'type' => 'sakit',
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(2),
            'reason' => 'Sakit',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->delete("/cuti/{$leave->id}");

        $response->assertStatus(403);
    }

    // ==========================================
    // STAFF PSDM: APPROVE/REJECT CUTI
    // ==========================================

    /** @test */
    public function staff_psdm_can_view_leave_management()
    {
        $response = $this->actingAs($this->staffPsdm)->get('/staff/psdm/leaves');
        $response->assertStatus(200);
    }

    /** @test */
    public function staff_psdm_can_approve_leave()
    {
        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'sakit',
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(2),
            'reason' => 'Sakit',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->staffPsdm)
            ->patch("/staff/psdm/leaves/{$leave->id}/approve");

        $response->assertRedirect();
        $this->assertDatabaseHas('leaves', [
            'id' => $leave->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function staff_psdm_can_reject_leave_with_reason()
    {
        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'cuti_tahunan',
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(5),
            'reason' => 'Liburan',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->staffPsdm)
            ->patch("/staff/psdm/leaves/{$leave->id}/reject", [
                'rejection_reason' => 'Kuota cuti habis',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leaves', [
            'id' => $leave->id,
            'status' => 'rejected',
            'rejection_reason' => 'Kuota cuti habis',
        ]);
    }

    /** @test */
    public function regular_user_cannot_approve_leave()
    {
        $leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'sakit',
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(2),
            'reason' => 'Sakit',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->patch("/staff/psdm/leaves/{$leave->id}/approve");

        $response->assertRedirect();
    }
}
