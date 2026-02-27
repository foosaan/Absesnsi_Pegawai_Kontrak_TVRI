<?php

namespace App\Exports;

use App\Models\User;
use App\Models\DeductionType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalaryTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $deductionTypes;

    public function __construct()
    {
        $this->deductionTypes = DeductionType::where('is_active', true)->orderBy('id')->get();
    }

    public function collection()
    {
        $types = $this->deductionTypes;

        return User::where('role', 'user')
            ->select('nip', 'name')
            ->orderBy('name')
            ->get()
            ->map(function ($user) use ($types) {
                $row = [
                    'nip' => $user->nip ?? '',
                    'nama' => $user->name,
                    'gaji_pokok' => '',
                    'potongan_kppn' => '',
                ];

                // Tambah kolom untuk setiap jenis potongan intern
                foreach ($types as $type) {
                    $row['deduction_' . $type->id] = '';
                }

                $row['gaji_diterima'] = '';

                return $row;
            });
    }

    public function headings(): array
    {
        $headings = [
            'NIP',
            'NAMA',
            'GAJI POKOK',
            'POTONGAN KPPN',
        ];

        // Tambah header untuk setiap jenis potongan intern
        foreach ($this->deductionTypes as $type) {
            $headings[] = strtoupper($type->name);
        }

        $headings[] = 'GAJI DITERIMA';

        return $headings;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 20,  // NIP
            'B' => 35,  // NAMA
            'C' => 18,  // GAJI POKOK
            'D' => 18,  // POTONGAN KPPN
        ];

        // Kolom dinamis untuk potongan intern
        $col = 'E';
        foreach ($this->deductionTypes as $type) {
            $widths[$col] = 16;
            $col++;
        }

        // GAJI DITERIMA
        $widths[$col] = 18;

        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4CAF50']
                ],
            ],
        ];
    }
}
