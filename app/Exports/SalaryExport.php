<?php

namespace App\Exports;

use App\Models\Salary;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalaryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        return Salary::with(['user', 'salaryDeductions.type'])
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIP',
            'Nama Karyawan',
            'Jabatan',
            'Bagian',
            'Status Pegawai',
            'Gaji Pokok',
            'Potongan KPPN',
            'Total Potongan Intern',
            'Gaji Diterima',
            'Status',
        ];
    }

    public function map($salary): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $salary->user->nip ?? '-',
            $salary->user->name,
            $salary->user->jabatan ?? '-',
            $salary->user->bagian ?? '-',
            $salary->user->status_pegawai ?? '-',
            $salary->base_salary,
            $salary->potongan_kppn,
            $salary->total_potongan_intern,
            $salary->final_salary,
            $this->getStatusLabel($salary->status),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function getStatusLabel($status)
    {
        return match($status) {
            'draft' => 'Draft',
            'dibayar' => 'Dibayar',
            default => $status,
        };
    }
}
