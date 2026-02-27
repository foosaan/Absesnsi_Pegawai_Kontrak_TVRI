<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // leave_approved, leave_rejected, trip_approved, trip_rejected, salary, announcement, pending_leave, pending_trip
            $table->string('icon')->default('fa-bell');
            $table->string('color')->default('text-gray-500');
            $table->string('message');
            $table->string('detail')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
