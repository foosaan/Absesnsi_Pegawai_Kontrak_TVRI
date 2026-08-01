<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\BusinessTrip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BusinessTripModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'user']);
    }

    // ==========================================
    // TEST: getStatusLabelAttribute()
    // ==========================================

    /** @test */
    public function status_label_returns_menunggu_persetujuan_for_pending()
    {
        $trip = BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Jakarta',
            'purpose' => 'Rapat',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(2),
            'status' => 'pending',
        ]);

        $this->assertEquals('Menunggu Persetujuan', $trip->status_label);
    }

    /** @test */
    public function status_label_returns_disetujui_for_approved()
    {
        $trip = BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Surabaya',
            'purpose' => 'Pelatihan',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDay(),
            'status' => 'approved',
        ]);

        $this->assertEquals('Disetujui', $trip->status_label);
    }

    /** @test */
    public function status_label_returns_ditolak_for_rejected()
    {
        $trip = BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Bandung',
            'purpose' => 'Survey',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'status' => 'rejected',
        ]);

        $this->assertEquals('Ditolak', $trip->status_label);
    }

    // ==========================================
    // TEST: getTotalDaysAttribute()
    // ==========================================

    /** @test */
    public function total_days_returns_1_for_single_day_trip()
    {
        $trip = BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Bogor',
            'purpose' => 'Rapat',
            'start_date' => Carbon::create(2026, 7, 10),
            'end_date' => Carbon::create(2026, 7, 10),
            'status' => 'pending',
        ]);

        $this->assertEquals(1, $trip->total_days);
    }

    /** @test */
    public function total_days_returns_correct_count_for_multi_day_trip()
    {
        $trip = BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Bali',
            'purpose' => 'Konferensi',
            'start_date' => Carbon::create(2026, 7, 10),
            'end_date' => Carbon::create(2026, 7, 13),
            'status' => 'approved',
        ]);

        $this->assertEquals(4, $trip->total_days);
    }

    // ==========================================
    // TEST: Scopes
    // ==========================================

    /** @test */
    public function scope_pending_returns_only_pending_trips()
    {
        BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Jakarta',
            'purpose' => 'A',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'status' => 'pending',
        ]);

        BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Surabaya',
            'purpose' => 'B',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(5),
            'status' => 'approved',
        ]);

        $pendingTrips = BusinessTrip::pending()->get();

        $this->assertCount(1, $pendingTrips);
        $this->assertEquals('pending', $pendingTrips->first()->status);
    }

    /** @test */
    public function scope_approved_returns_only_approved_trips()
    {
        BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Jakarta',
            'purpose' => 'A',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'status' => 'pending',
        ]);

        BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Surabaya',
            'purpose' => 'B',
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(5),
            'status' => 'approved',
        ]);

        $approvedTrips = BusinessTrip::approved()->get();

        $this->assertCount(1, $approvedTrips);
        $this->assertEquals('approved', $approvedTrips->first()->status);
    }

    // ==========================================
    // TEST: Relasi
    // ==========================================

    /** @test */
    public function trip_belongs_to_user()
    {
        $trip = BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Jakarta',
            'purpose' => 'Test',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(User::class, $trip->user);
        $this->assertEquals($this->user->id, $trip->user->id);
    }

    /** @test */
    public function trip_belongs_to_approver()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $trip = BusinessTrip::create([
            'user_id' => $this->user->id,
            'destination' => 'Jakarta',
            'purpose' => 'Test',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        $this->assertInstanceOf(User::class, $trip->approver);
        $this->assertEquals($admin->id, $trip->approver->id);
    }
}
