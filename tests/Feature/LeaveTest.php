<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Leave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeaveTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'user']);
    }

    public function test_user_can_view_leave_list(): void
    {
        $response = $this->actingAs($this->user)->get(route('user.leaves'));

        $response->assertStatus(200);
    }

    public function test_user_can_view_create_leave_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('user.leaves.create'));

        $response->assertStatus(200);
    }

    public function test_user_can_submit_leave_without_attachment(): void
    {
        $response = $this->actingAs($this->user)->post(route('user.leaves.store'), [
            'type' => 'cuti_tahunan',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'reason' => 'Liburan keluarga',
        ]);

        $response->assertRedirect(route('user.leaves'));
        $this->assertDatabaseHas('leaves', [
            'user_id' => $this->user->id,
            'type' => 'cuti_tahunan',
            'reason' => 'Liburan keluarga',
            'status' => 'pending',
        ]);
    }

    public function test_user_can_submit_leave_with_attachment(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('surat_dokter.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->user)->post(route('user.leaves.store'), [
            'type' => 'sakit',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'reason' => 'Sakit demam',
            'attachment' => $file,
        ]);

        $response->assertRedirect(route('user.leaves'));

        $leave = Leave::where('user_id', $this->user->id)->first();
        $this->assertNotNull($leave);
        $this->assertNotNull($leave->attachment);
    }

    public function test_leave_attachment_rejects_invalid_file_type(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('virus.exe', 1024);

        $response = $this->actingAs($this->user)->post(route('user.leaves.store'), [
            'type' => 'sakit',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'reason' => 'Sakit',
            'attachment' => $file,
        ]);

        $response->assertSessionHasErrors('attachment');
    }

    public function test_leave_requires_type(): void
    {
        $response = $this->actingAs($this->user)->post(route('user.leaves.store'), [
            'type' => '',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'reason' => 'Test',
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_leave_requires_reason(): void
    {
        $response = $this->actingAs($this->user)->post(route('user.leaves.store'), [
            'type' => 'cuti_tahunan',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'reason' => '',
        ]);

        $response->assertSessionHasErrors('reason');
    }

    public function test_leave_end_date_must_be_after_start_date(): void
    {
        $response = $this->actingAs($this->user)->post(route('user.leaves.store'), [
            'type' => 'cuti_tahunan',
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(1)->format('Y-m-d'),
            'reason' => 'Test',
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_guest_cannot_access_leave_page(): void
    {
        $response = $this->get(route('user.leaves'));

        $response->assertRedirect('/login');
    }
}
