<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterData;

class MigrateMasterDataSeeder extends Seeder
{
    /**
     * Seed default Master Data PSDM
     */
    public function run(): void
    {
        // 1. Seed Jabatan
        $jabatans = [
            'Staff Administrasi',
            'Teknisi Siaran',
            'Produser',
            'Reporter',
            'Kamerawan',
            'Editor Video',
            'Pengamanan (Satpam)',
            'Pramubakti (OB)',
            'Pengemudi (Driver)'
        ];
        foreach ($jabatans as $jabatan) {
            MasterData::firstOrCreate([
                'type' => 'jabatan',
                'value' => $jabatan,
            ], [
                'description' => 'Jabatan ' . $jabatan,
                'is_active' => true,
            ]);
        }
        $this->command->info("✓ Seeded " . count($jabatans) . " jabatan records");

        // 2. Seed Bagian
        $bagians = [
            'Sub Bagian PSDM (SDM)',
            'Sub Bagian Keuangan',
            'Seksi Berita',
            'Seksi Pengembangan Usaha',
            'Seksi Transmisi',
            'Seksi Produksi & Siaran',
            'Umum & Rumah Tangga'
        ];
        foreach ($bagians as $bagian) {
            MasterData::firstOrCreate([
                'type' => 'bagian',
                'value' => $bagian,
            ], [
                'description' => 'Bagian/Seksi ' . $bagian,
                'is_active' => true,
            ]);
        }
        $this->command->info("✓ Seeded " . count($bagians) . " bagian records");

        // 3. Seed Status Pegawai
        $statuses = [
            'PPNPN (Pegawai Pemerintah Non Pegawai Negeri)',
            'Pegawai Kontrak TVRI',
            'Magang',
            'Tenaga Ahli'
        ];
        foreach ($statuses as $status) {
            MasterData::firstOrCreate([
                'type' => 'status_pegawai',
                'value' => $status,
            ], [
                'description' => 'Status kepegawaian ' . $status,
                'is_active' => true,
            ]);
        }
        $this->command->info("✓ Seeded " . count($statuses) . " status pegawai records");

        // 4. Seed Status Operasional
        $opsList = ['Shift', 'Non-Shift'];
        foreach ($opsList as $ops) {
            MasterData::firstOrCreate([
                'type' => 'status_operasional',
                'value' => $ops,
            ], [
                'description' => 'Status operasional kerja ' . $ops,
                'is_active' => true,
            ]);
        }
        $this->command->info("✓ Seeded status operasional records");

        $this->command->info("\n🎉 Master Data PSDM seeding completed successfully!");
    }
}
