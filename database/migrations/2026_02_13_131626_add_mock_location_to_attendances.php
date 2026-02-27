<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->float('location_accuracy')->nullable()->after('longitude');
            $table->float('check_out_location_accuracy')->nullable()->after('check_out_longitude');
            $table->boolean('is_mock_location')->default(false)->after('check_out_location_accuracy');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['location_accuracy', 'check_out_location_accuracy', 'is_mock_location']);
        });
    }
};
