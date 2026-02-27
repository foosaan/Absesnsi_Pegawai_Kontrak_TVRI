<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeductionType;

class DeductionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            [
                'name' => 'Koperasi',
                'description' => 'Potongan simpanan atau pinjaman koperasi',
                'is_active' => true,
            ],
            [
                'name' => 'Denda Keterlambatan',
                'description' => 'Potongan karena keterlambatan kehadiran',
                'is_active' => true,
            ],
            [
                'name' => 'Kasbon',
                'description' => 'Potongan pengembalian kasbon/pinjaman kantor',
                'is_active' => true,
            ],
            [
                'name' => 'BPJS Kesehatan',
                'description' => 'Iuran BPJS Kesehatan',
                'is_active' => true,
            ],
            [
                'name' => 'BPJS Ketenagakerjaan',
                'description' => 'Iuran BPJS Ketenagakerjaan',
                'is_active' => true,
            ],
            [
                'name' => 'Lain-lain',
                'description' => 'Potongan lainnya',
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            DeductionType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
