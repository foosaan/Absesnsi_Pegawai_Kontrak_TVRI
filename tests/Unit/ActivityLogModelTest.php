<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityLogModelTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // TEST: getActionColorAttribute()
    // ==========================================

    /** @test */
    public function action_color_returns_green_for_create()
    {
        $log = new ActivityLog(['action' => 'create']);

        $this->assertEquals('green', $log->action_color);
    }

    /** @test */
    public function action_color_returns_blue_for_update()
    {
        $log = new ActivityLog(['action' => 'update']);

        $this->assertEquals('blue', $log->action_color);
    }

    /** @test */
    public function action_color_returns_red_for_delete()
    {
        $log = new ActivityLog(['action' => 'delete']);

        $this->assertEquals('red', $log->action_color);
    }

    /** @test */
    public function action_color_returns_gray_for_unknown_action()
    {
        $log = new ActivityLog(['action' => 'export']);

        $this->assertEquals('gray', $log->action_color);
    }

    // ==========================================
    // TEST: getActionIconAttribute()
    // ==========================================

    /** @test */
    public function action_icon_returns_plus_for_create()
    {
        $log = new ActivityLog(['action' => 'create']);

        $this->assertEquals('➕', $log->action_icon);
    }

    /** @test */
    public function action_icon_returns_pencil_for_update()
    {
        $log = new ActivityLog(['action' => 'update']);

        $this->assertEquals('✏️', $log->action_icon);
    }

    /** @test */
    public function action_icon_returns_trash_for_delete()
    {
        $log = new ActivityLog(['action' => 'delete']);

        $this->assertEquals('🗑️', $log->action_icon);
    }

    /** @test */
    public function action_icon_returns_notepad_for_unknown_action()
    {
        $log = new ActivityLog(['action' => 'export']);

        $this->assertEquals('📝', $log->action_icon);
    }

    // ==========================================
    // TEST: Relasi
    // ==========================================

    /** @test */
    public function activity_log_belongs_to_user()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $log = ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'create',
            'model_type' => 'App\Models\User',
            'model_id' => $user->id,
            'description' => 'Test log',
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertInstanceOf(User::class, $log->user);
        $this->assertEquals($user->id, $log->user->id);
    }

    // ==========================================
    // TEST: JSON cast (old_values, new_values)
    // ==========================================

    /** @test */
    public function old_and_new_values_are_cast_as_arrays()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $log = ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'update',
            'model_type' => 'App\Models\Shift',
            'model_id' => 1,
            'description' => 'Update shift',
            'old_values' => ['start_time' => '07:00:00'],
            'new_values' => ['start_time' => '08:00:00'],
            'ip_address' => '127.0.0.1',
        ]);

        $log->refresh();

        $this->assertIsArray($log->old_values);
        $this->assertIsArray($log->new_values);
        $this->assertEquals('07:00:00', $log->old_values['start_time']);
        $this->assertEquals('08:00:00', $log->new_values['start_time']);
    }
}
