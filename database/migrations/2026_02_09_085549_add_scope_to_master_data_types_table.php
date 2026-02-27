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
        Schema::table('master_data_types', function (Blueprint $table) {
            $table->string('scope')->default('admin')->after('slug'); // admin, psdm, keuangan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_data_types', function (Blueprint $table) {
            $table->dropColumn('scope');
        });
    }
};
