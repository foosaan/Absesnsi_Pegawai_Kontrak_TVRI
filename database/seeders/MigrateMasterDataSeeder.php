<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterDataType;
use App\Models\MasterDataValue;
use App\Models\Jabatan;
use App\Models\Bagian;
use App\Models\StatusPegawai;

class MigrateMasterDataSeeder extends Seeder
{
    /**
     * Migrate data dari tabel lama (jabatan, bagian, status_pegawai) ke Master Data PSDM
     */
    public function run(): void
    {
        // 1. Migrate Jabatan
        $jabatanType = MasterDataType::firstOrCreate(
            ['slug' => 'jabatan'],
            [
                'name' => 'Jabatan',
                'scope' => 'psdm',
                'description' => 'Daftar jabatan pegawai',
                'is_active' => true,
            ]
        );
        
        $jabatanList = Jabatan::all();
        foreach ($jabatanList as $jabatan) {
            MasterDataValue::firstOrCreate(
                [
                    'master_data_type_id' => $jabatanType->id,
                    'value' => $jabatan->name,
                ],
                [
                    'description' => null,
                    'is_active' => $jabatan->is_active ?? true,
                ]
            );
        }
        $this->command->info("✓ Migrated " . $jabatanList->count() . " jabatan records");

        // 2. Migrate Bagian
        $bagianType = MasterDataType::firstOrCreate(
            ['slug' => 'bagian'],
            [
                'name' => 'Bagian',
                'scope' => 'psdm',
                'description' => 'Daftar bagian/departemen',
                'is_active' => true,
            ]
        );
        
        $bagianList = Bagian::all();
        foreach ($bagianList as $bagian) {
            MasterDataValue::firstOrCreate(
                [
                    'master_data_type_id' => $bagianType->id,
                    'value' => $bagian->name,
                ],
                [
                    'description' => null,
                    'is_active' => $bagian->is_active ?? true,
                ]
            );
        }
        $this->command->info("✓ Migrated " . $bagianList->count() . " bagian records");

        // 3. Migrate Status Pegawai
        $statusType = MasterDataType::firstOrCreate(
            ['slug' => 'status-pegawai'],
            [
                'name' => 'Status Pegawai',
                'scope' => 'psdm',
                'description' => 'Daftar status kepegawaian',
                'is_active' => true,
            ]
        );
        
        $statusList = StatusPegawai::all();
        foreach ($statusList as $status) {
            MasterDataValue::firstOrCreate(
                [
                    'master_data_type_id' => $statusType->id,
                    'value' => $status->name,
                ],
                [
                    'description' => null,
                    'is_active' => $status->is_active ?? true,
                ]
            );
        }
        $this->command->info("✓ Migrated " . $statusList->count() . " status pegawai records");

        $this->command->info("\n🎉 Master Data PSDM migration completed!");
    }
}
