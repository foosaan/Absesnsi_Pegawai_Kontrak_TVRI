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
        Schema::table('sessions', function (Blueprint $table) {
            // Check if user_id column exists before defining constraints
            if (Schema::hasColumn('sessions', 'user_id')) {
                // Add foreign key pointing to users(id) with cascade deletion
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            if (Schema::hasColumn('sessions', 'user_id')) {
                $table->dropForeign(['user_id']);
            }
        });
    }
};
