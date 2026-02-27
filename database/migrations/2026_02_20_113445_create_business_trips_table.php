<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('destination'); // tujuan dinas luar
            $table->text('purpose'); // keperluan/alasan
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        // Add business_trip_id to attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('business_trip_id')->nullable()->after('leave_id')->constrained('business_trips')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['business_trip_id']);
            $table->dropColumn('business_trip_id');
        });
        Schema::dropIfExists('business_trips');
    }
};
