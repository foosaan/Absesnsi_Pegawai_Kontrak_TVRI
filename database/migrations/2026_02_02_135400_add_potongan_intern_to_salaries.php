<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan field potongan intern sesuai slip gaji TVRI
     */
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            // Potongan KPPN
            $table->decimal('potongan_kppn', 15, 2)->default(0)->after('base_salary');
            
            // Potongan Intern
            $table->decimal('simpanan_wajib', 15, 2)->default(0)->after('potongan_kppn');
            $table->decimal('kredit_uang', 15, 2)->default(0)->after('simpanan_wajib');
            $table->decimal('kredit_toko', 15, 2)->default(0)->after('kredit_uang');
            $table->decimal('dharma_wanita', 15, 2)->default(0)->after('kredit_toko');
            $table->decimal('bpjs', 15, 2)->default(0)->after('dharma_wanita');
            
            // Total potongan intern (calculated)
            $table->decimal('total_potongan_intern', 15, 2)->default(0)->after('bpjs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn([
                'potongan_kppn',
                'simpanan_wajib',
                'kredit_uang',
                'kredit_toko',
                'dharma_wanita',
                'bpjs',
                'total_potongan_intern',
            ]);
        });
    }
};
