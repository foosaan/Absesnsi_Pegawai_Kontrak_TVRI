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
            $columnsToDrop = [];
            if (Schema::hasColumn('users', 'division')) {
                $columnsToDrop[] = 'division';
            }
            if (Schema::hasColumn('users', 'nomor_sk')) {
                $columnsToDrop[] = 'nomor_sk';
            }
            if (Schema::hasColumn('users', 'tanggal_sk')) {
                $columnsToDrop[] = 'tanggal_sk';
            }
            if (Schema::hasColumn('users', 'npwp')) {
                $columnsToDrop[] = 'npwp';
            }
            if (Schema::hasColumn('users', 'status_pajak')) {
                $columnsToDrop[] = 'status_pajak';
            }
            if (Schema::hasColumn('users', 'nomor_rekening')) {
                $columnsToDrop[] = 'nomor_rekening';
            }
            if (Schema::hasColumn('users', 'nama_bank')) {
                $columnsToDrop[] = 'nama_bank';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('division', ['keuangan', 'psdm'])->nullable()->after('status_operasional');
            $table->string('nomor_sk', 50)->nullable()->after('status_pegawai');
            $table->date('tanggal_sk')->nullable()->after('nomor_sk');
            $table->string('npwp', 30)->nullable()->after('nik');
            $table->string('status_pajak', 10)->nullable()->after('tanggal_sk');
            $table->string('nomor_rekening', 30)->nullable()->after('status_pajak');
            $table->string('nama_bank', 50)->nullable()->after('nomor_rekening');
        });
    }
};
