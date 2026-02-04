<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalaryTemplateExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        // Get all users (employees) for template
        return User::where('role', 'user')
            ->select('nip', 'name')
            ->get()
            ->map(function ($user) {
                return [
                    'nip' => $user->nip ?? '',
                    'nama' => $user->name,
                    'gaji_pokok' => '',
                    'potongan_kppn' => '',
                    'simpanan_wajib' => '',
                    'kredit_uang' => '',
                    'kredit_toko' => '',
                    'dharma_wanita' => '',
                    'bpjs' => '',
                    'gaji_diterima' => '',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'NIP',
            'Nama',
            'Gaji Pokok',
            'Potongan KPPN',
            'Simpanan Wajib',
            'Kredit Uang',
            'Kredit Toko',
            'Dharma Wanita',
            'BPJS',
            'Gaji Diterima',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4CAF50']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]
            ],
        ];
    }
}
