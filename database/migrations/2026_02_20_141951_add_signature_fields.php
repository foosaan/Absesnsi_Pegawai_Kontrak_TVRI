<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add signature image path to users (for staff keuangan)
        Schema::table('users', function (Blueprint $table) {
            $table->string('signature')->nullable()->after('profile_photo');
        });

        // Add signing fields to salaries
        Schema::table('salaries', function (Blueprint $table) {
            $table->unsignedBigInteger('signed_by')->nullable()->after('status');
            $table->timestamp('signed_at')->nullable()->after('signed_by');

            $table->foreign('signed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropForeign(['signed_by']);
            $table->dropColumn(['signed_by', 'signed_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('signature');
        });
    }
};
