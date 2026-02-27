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
            $table->string('jabatan')->nullable()->after('jenis_kelamin');
            $table->string('bagian')->nullable()->after('jabatan');
            $table->string('status_operasional')->nullable()->after('bagian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['jabatan', 'bagian', 'status_operasional']);
        });
    }
};
