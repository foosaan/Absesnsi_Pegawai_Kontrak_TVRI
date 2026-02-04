<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->timestamp('check_out_time')->nullable()->after('check_in_time');
            $table->string('check_out_photo_path')->nullable()->after('photo_path');
            $table->decimal('check_out_latitude', 10, 8)->nullable()->after('longitude');
            $table->decimal('check_out_longitude', 11, 8)->nullable()->after('check_out_latitude');
        });

        // Add time limit settings
        \Illuminate\Support\Facades\DB::table('settings')->insert([
            ['key' => 'check_in_start', 'value' => '07:00', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'check_in_end', 'value' => '09:00', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'check_out_start', 'value' => '16:00', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'check_out_end', 'value' => '18:00', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['check_out_time', 'check_out_photo_path', 'check_out_latitude', 'check_out_longitude']);
        });

        \Illuminate\Support\Facades\DB::table('settings')->whereIn('key', [
            'check_in_start', 'check_in_end', 'check_out_start', 'check_out_end'
        ])->delete();
    }
};
