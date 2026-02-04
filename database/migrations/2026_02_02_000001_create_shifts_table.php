<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Normal", "Shift 1", "Shift 2", "Shift 3"
            $table->string('type'); // "normal" or "shift"
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('tolerance_minutes')->default(30);
            $table->timestamps();
        });

        // Seed default shifts
        DB::table('shifts')->insert([
            [
                'name' => 'Normal (OB)',
                'type' => 'normal',
                'start_time' => '07:00:00',
                'end_time' => '15:00:00',
                'tolerance_minutes' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shift 1',
                'type' => 'shift',
                'start_time' => '00:00:00',
                'end_time' => '08:00:00',
                'tolerance_minutes' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shift 2',
                'type' => 'shift',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'tolerance_minutes' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shift 3',
                'type' => 'shift',
                'start_time' => '16:00:00',
                'end_time' => '23:59:59',
                'tolerance_minutes' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
