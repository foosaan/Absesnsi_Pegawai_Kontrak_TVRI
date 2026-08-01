<?php

namespace App\Exports;

use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filterType;
    protected $month;
    protected $year;
    protected $date;
    protected $userId;
    protected $no = 0;

    /**
     * @param string $filterType 'day', 'month', or 'all'
     * @param array $params ['month' => ..., 'year' => ..., 'date' => ..., 'user_id' => ...]
     */
    public function __construct(string $filterType = 'month', array $params = [])
    {
        $this->filterType = $filterType;
        $this->month = $params['month'] ?? Carbon::now()->month;
        $this->year = $params['year'] ?? Carbon::now()->year;
        $this->date = $params['date'] ?? null;
        $this->userId = $params['user_id'] ?? null;
    }

    public function collection()
    {
        $query = Attendance::with(['user', 'shift'])
            ->orderBy('check_in_time');

        switch ($this->filterType) {
            case 'day':
                if ($this->date) {
                    $query->where('work_date', $this->date);
                }
                break;
            case 'month':
                $query->whereMonth('work_date', $this->month)
                      ->whereYear('work_date', $this->year);
                break;
            case 'all':
                // No date filter — export all data
                break;
        }

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        } else {
            $query->orderBy('user_id');
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Hari',
            'NIP',
            'Nama Pegawai',
            'Jabatan',
            'Bagian',
            'Shift',
            'Jam Masuk',
            'Jam Pulang',
            'Status',
        ];
    }

    public function map($attendance): array
    {
        $this->no++;

        Carbon::setLocale('id');

        return [
            $this->no,
            $attendance->work_date ? $attendance->work_date->format('d/m/Y') : ($attendance->check_in_time ? $attendance->check_in_time->format('d/m/Y') : '-'),
            $attendance->work_date ? $attendance->work_date->translatedFormat('l') : ($attendance->check_in_time ? $attendance->check_in_time->translatedFormat('l') : '-'),
            $attendance->user->nip ?? '-',
            $attendance->user->name ?? '-',
            $attendance->user->jabatan ?? '-',
            $attendance->user->bagian ?? '-',
            $attendance->shift->name ?? '-',
            $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-',
            $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-',
            $this->getStatusLabel($attendance->status),
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
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'left' => 'Pulang',
            'absent' => 'Tidak Hadir',
            'cuti' => 'Cuti',
            'leave' => 'Cuti',
            'dinas_luar' => 'Dinas Luar',
            default => $status ?? '-',
        };
    }
}
