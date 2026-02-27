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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'staff_psdm', 'staff_keuangan', 'user'])->default('user')->after('email');
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('photo_path');
            $table->timestamp('check_in_time');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('status')->default('present'); // e.g., present, late
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        
        // Seed default settings for TVRI Jogja (approximate location, can be updated by admin)
        DB::table('settings')->insert([
            ['key' => 'office_latitude', 'value' => '-7.766723', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'office_longitude', 'value' => '110.377012', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'allowed_radius_meters', 'value' => '50', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('attendances');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
