<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\BusinessTrip;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BusinessTripFeatureTest extends TestCase
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
    // USER: AJUKAN DINAS LUAR
    // ==========================================

    /** @test */
    public function user_can_view_business_trip_list()
    {
        $response = $this->actingAs($this->user)->get('/dinas-luar');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_view_create_business_trip_form()
    {
        $response = $this->actingAs($this->user)->get('/dinas-luar/create');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_submit_business_trip()
    {
        Storage::fake('public');

        $response = $this->actingAs($this->user)->post('/dinas-luar', [
            'destination' => 'Jakarta',
            'purpose' => 'Rapat koordinasi',
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->addDays(3)->toDateString(),
            'attachment' => UploadedFile::fake()->create('surat_tugas.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('user.business-trips'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('business_trips', [
            'user_id' => $this->user->id,
            'destination' => 'Jakarta',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function user_cannot_submit_business_trip_without_destination()
    {
        $response = $this->actingAs($this->user)->post('/dinas-luar', [
            'purpose' => 'Rapat',
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
        ]);

        $response->assertSessionHasErrors('destination');
    }

    /** @test */
    public function user_cannot_submit_overlapping_business_trip()
    {
        BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Jakarta',
            'purpose' => 'Rapat',
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(3),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->post('/dinas-luar', [
            'destination' => 'Surabaya',
            'purpose' => 'Workshop',
            'start_date' => Carbon::tomorrow()->addDay()->toDateString(),
            'end_date' => Carbon::tomorrow()->addDays(5)->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function user_can_cancel_pending_business_trip()
    {
        $trip = BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Jakarta',
            'purpose' => 'Rapat',
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(2),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->delete("/dinas-luar/{$trip->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('business_trips', ['id' => $trip->id]);
    }

    /** @test */
    public function user_cannot_cancel_other_users_trip()
    {
        $otherUser = User::factory()->create(['role' => 'user']);
        $trip = BusinessTrip::create([
            'user_id' => $otherUser->id,
            'destination' => 'Jakarta',
            'purpose' => 'Rapat',
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(2),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->delete("/dinas-luar/{$trip->id}");
        $response->assertStatus(403);
    }

    // ==========================================
    // STAFF PSDM: APPROVE/REJECT DINAS LUAR
    // ==========================================

    /** @test */
    public function staff_psdm_can_view_business_trip_management()
    {
        $response = $this->actingAs($this->staffPsdm)->get('/staff/psdm/business-trips');
        $response->assertStatus(200);
    }

    /** @test */
    public function staff_psdm_can_approve_business_trip()
    {
        $trip = BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Jakarta',
            'purpose' => 'Rapat',
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(2),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->staffPsdm)
            ->patch("/staff/psdm/business-trips/{$trip->id}/approve");

        $response->assertRedirect();
        $this->assertDatabaseHas('business_trips', [
            'id' => $trip->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function staff_psdm_can_reject_business_trip()
    {
        $trip = BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Jakarta',
            'purpose' => 'Rapat',
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(2),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->staffPsdm)
            ->patch("/staff/psdm/business-trips/{$trip->id}/reject", [
                'rejection_reason' => 'Tidak disetujui pimpinan',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('business_trips', [
            'id' => $trip->id,
            'status' => 'rejected',
        ]);
    }

    /** @test */
    public function regular_user_cannot_approve_business_trip()
    {
        $trip = BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Jakarta',
            'purpose' => 'Rapat',
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(2),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->patch("/staff/psdm/business-trips/{$trip->id}/approve");

        $response->assertRedirect();
    }
}
