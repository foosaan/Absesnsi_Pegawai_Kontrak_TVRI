<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added this line

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Seed initial deduction types
        $types = [
            'simpanan_wajib' => 'Simpanan Wajib',
            'kredit_uang' => 'Kredit Uang',
            'kredit_toko' => 'Kredit Toko',
            'dharma_wanita' => 'Dharma Wanita',
            'bpjs' => 'BPJS',
        ];

        foreach ($types as $key => $name) {
            DB::table('deduction_types')->insert([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Move existing data to new table
        $salaries = DB::table('salaries')->get();
        
        foreach ($salaries as $salary) {
            foreach ($types as $column => $name) {
                // Check if column exists and has value
                if (Schema::hasColumn('salaries', $column) && $salary->$column > 0) {
                    $typeId = DB::table('deduction_types')->where('name', $name)->value('id');
                    
                    DB::table('salary_deductions')->insert([
                        'salary_id' => $salary->id,
                        'deduction_type_id' => $typeId,
                        'amount' => $salary->$column,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 3. Drop old columns
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn([
                'simpanan_wajib',
                'kredit_uang',
                'kredit_toko',
                'dharma_wanita',
                'bpjs',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Restore columns
        Schema::table('salaries', function (Blueprint $table) {
            $table->decimal('simpanan_wajib', 15, 2)->default(0)->after('potongan_kppn');
            $table->decimal('kredit_uang', 15, 2)->default(0)->after('simpanan_wajib');
            $table->decimal('kredit_toko', 15, 2)->default(0)->after('kredit_uang');
            $table->decimal('dharma_wanita', 15, 2)->default(0)->after('kredit_toko');
            $table->decimal('bpjs', 15, 2)->default(0)->after('dharma_wanita');
        });

        // 2. Restore data from salary_deductions to salaries columns
        $deductions = DB::table('salary_deductions')
            ->join('deduction_types', 'salary_deductions.deduction_type_id', '=', 'deduction_types.id')
            ->select('salary_deductions.*', 'deduction_types.name as type_name')
            ->get();

        $typeMap = [
            'Simpanan Wajib' => 'simpanan_wajib',
            'Kredit Uang' => 'kredit_uang',
            'Kredit Toko' => 'kredit_toko',
            'Dharma Wanita' => 'dharma_wanita',
            'BPJS' => 'bpjs',
        ];

        foreach ($deductions as $deduction) {
            if (isset($typeMap[$deduction->type_name])) {
                DB::table('salaries')
                    ->where('id', $deduction->salary_id)
                    ->update([
                        $typeMap[$deduction->type_name] => $deduction->amount
                    ]);
            }
        }
    }
};
