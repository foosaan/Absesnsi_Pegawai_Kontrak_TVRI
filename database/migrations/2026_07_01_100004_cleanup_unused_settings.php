<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')->whereIn('key', [
            'check_in_start',
            'check_in_end',
            'check_out_start',
            'check_out_end'
        ])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->insert([
            ['key' => 'check_in_start', 'value' => '07:00', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'check_in_end', 'value' => '09:00', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'check_out_start', 'value' => '16:00', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'check_out_end', 'value' => '18:00', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
};
