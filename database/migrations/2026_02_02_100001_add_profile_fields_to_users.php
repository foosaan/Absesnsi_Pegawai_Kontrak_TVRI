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
            // Identitas Pegawai
            $table->string('nip', 20)->nullable()->after('employee_type')->comment('Nomor Induk Pegawai');
            $table->string('npwp', 30)->nullable()->after('nip');
            $table->string('status_pegawai', 20)->nullable()->after('npwp')->comment('tetap/kontrak');
            $table->string('nomor_sk', 50)->nullable()->after('status_pegawai');
            $table->date('tanggal_sk')->nullable()->after('nomor_sk');
            
            // Data Pajak
            $table->string('status_pajak', 10)->nullable()->after('tanggal_sk')->comment('TK/K1/K2/K3');
            
            // Data Bank
            $table->string('nomor_rekening', 30)->nullable()->after('status_pajak');
            $table->string('nama_bank', 50)->nullable()->after('nomor_rekening');
            
            // Gaji Pokok
            $table->decimal('gaji_pokok', 15, 2)->nullable()->after('nama_bank');
            
            // Data Pribadi
            $table->text('alamat')->nullable()->after('gaji_pokok');
            $table->string('no_telepon', 20)->nullable()->after('alamat');
            $table->date('tanggal_lahir')->nullable()->after('no_telepon');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('tanggal_lahir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nip',
                'npwp',
                'status_pegawai',
                'nomor_sk',
                'tanggal_sk',
                'status_pajak',
                'nomor_rekening',
                'nama_bank',
                'gaji_pokok',
                'alamat',
                'no_telepon',
                'tanggal_lahir',
                'jenis_kelamin',
            ]);
        });
    }
};
