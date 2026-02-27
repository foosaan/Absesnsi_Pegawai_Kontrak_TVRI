<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BusinessTrip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessTripTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'user']);
    }

    public function test_user_can_view_business_trip_list(): void
    {
        $response = $this->actingAs($this->user)->get(route('user.business-trips'));

        $response->assertStatus(200);
    }

    public function test_user_can_view_create_business_trip_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('user.business-trips.create'));

        $response->assertStatus(200);
    }

    public function test_user_can_submit_business_trip_without_attachment(): void
    {
        $response = $this->actingAs($this->user)->post(route('user.business-trips.store'), [
            'destination' => 'Kantor Pusat Jakarta',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'purpose' => 'Rapat koordinasi tahunan',
        ]);

        $response->assertRedirect(route('user.business-trips'));
        $this->assertDatabaseHas('business_trips', [
            'user_id' => $this->user->id,
            'destination' => 'Kantor Pusat Jakarta',
            'purpose' => 'Rapat koordinasi tahunan',
            'status' => 'pending',
        ]);
    }

    public function test_user_can_submit_business_trip_with_attachment(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('surat_tugas.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->user)->post(route('user.business-trips.store'), [
            'destination' => 'TVRI Yogyakarta',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'purpose' => 'Liputan acara',
            'attachment' => $file,
        ]);

        $response->assertRedirect(route('user.business-trips'));

        $trip = BusinessTrip::where('user_id', $this->user->id)->first();
        $this->assertNotNull($trip);
        $this->assertNotNull($trip->attachment);
    }

    public function test_business_trip_attachment_rejects_invalid_file_type(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('malware.exe', 1024);

        $response = $this->actingAs($this->user)->post(route('user.business-trips.store'), [
            'destination' => 'Jakarta',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'purpose' => 'Test',
            'attachment' => $file,
        ]);

        $response->assertSessionHasErrors('attachment');
    }

    public function test_business_trip_requires_destination(): void
    {
        $response = $this->actingAs($this->user)->post(route('user.business-trips.store'), [
            'destination' => '',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'purpose' => 'Test',
        ]);

        $response->assertSessionHasErrors('destination');
    }

    public function test_business_trip_requires_purpose(): void
    {
        $response = $this->actingAs($this->user)->post(route('user.business-trips.store'), [
            'destination' => 'Jakarta',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'purpose' => '',
        ]);

        $response->assertSessionHasErrors('purpose');
    }

    public function test_business_trip_end_date_must_be_after_start_date(): void
    {
        $response = $this->actingAs($this->user)->post(route('user.business-trips.store'), [
            'destination' => 'Jakarta',
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(1)->format('Y-m-d'),
            'purpose' => 'Test',
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_overlapping_business_trips_are_rejected(): void
    {
        // Create first trip
        BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Jakarta',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'purpose' => 'First trip',
            'status' => 'pending',
        ]);

        // Try to create overlapping trip
        $response = $this->actingAs($this->user)->post(route('user.business-trips.store'), [
            'destination' => 'Surabaya',
            'start_date' => now()->addDays(3)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
            'purpose' => 'Second trip',
        ]);

        // Should be rejected with error
        $response->assertSessionHas('error');
    }

    public function test_guest_cannot_access_business_trip_page(): void
    {
        $response = $this->get(route('user.business-trips'));

        $response->assertRedirect('/login');
    }
}
