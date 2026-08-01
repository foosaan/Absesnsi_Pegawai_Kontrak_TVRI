<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * Data contoh (1 baris dummy) agar pengguna tahu cara mengisi.
     */
    public function array(): array
    {
        return [
            [
                '199001012020011001',   // NIP  (18 digit)
                '3201011234560001',     // NIK  (16 digit)
                'Budi Santoso',         // NAMA
                'budi.santoso@tvri.go.id', // EMAIL
                'Staff Administrasi',   // JABATAN
                'Seksi Berita',         // BAGIAN
                'PPNPN (Pegawai Pemerintah Non Pegawai Negeri)', // STATUS_PEGAWAI
                'L',                    // JENIS_KELAMIN
                'Jl. Magelang No. 10, Yogyakarta', // ALAMAT
                'normal',               // TIPE_PRESENSI
                '081234567890',         // NO_TELEPON
            ],
        ];
    }

    /**
     * Header kolom template.
     */
    public function headings(): array
    {
        return [
            'NIP',
            'NIK',
            'NAMA',
            'EMAIL',
            'JABATAN',
            'BAGIAN',
            'STATUS_PEGAWAI',
            'JENIS_KELAMIN',
            'ALAMAT',
            'TIPE_PRESENSI',
            'NO_TELEPON',
        ];
    }

    /**
     * Lebar kolom agar mudah dibaca.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 22,  // NIP
            'B' => 20,  // NIK
            'C' => 28,  // NAMA
            'D' => 30,  // EMAIL
            'E' => 24,  // JABATAN
            'F' => 28,  // BAGIAN
            'G' => 45,  // STATUS_PEGAWAI
            'H' => 16,  // JENIS_KELAMIN
            'I' => 40,  // ALAMAT
            'J' => 16,  // TIPE_PRESENSI
            'K' => 18,  // NO_TELEPON
        ];
    }

    /**
     * Styling: header row hijau emerald, baris contoh abu-abu terang.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Header row
            1 => [
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'], // emerald-600
                ],
            ],
            // Baris contoh data (italic, abu-abu)
            2 => [
                'font' => ['italic' => true, 'color' => ['rgb' => '6B7280']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F3F4F6'], // gray-100
                ],
            ],
        ];
    }
}
