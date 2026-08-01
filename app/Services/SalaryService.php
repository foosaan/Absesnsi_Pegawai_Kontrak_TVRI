<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Salary;
use Carbon\Carbon;

class SalaryService
{
    /**
     * Calculate salary for a user for a specific month
     */
    public function calculateSalary(User $user, int $month, int $year, float $baseSalary): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        // Dapatkan data presensi (kehadiran) untuk bulan tersebut
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->get();
        
        // Hitung statistik
        $totalWorkDays = $this->getWorkingDaysInMonth($year, $month);
        $daysPresent = $attendances->count();
        $daysLate = $attendances->where('status', 'late')->count();
        $daysAbsent = $totalWorkDays - $daysPresent;
        
        // Hitung potongan
        // Late & absent deductions are removed as per requirements (tidak ada denda otomatis).
        $lateDeduction = 0;
        $absentDeduction = 0;
        $totalDeductions = 0;
        
        $finalSalary = $baseSalary;
        
        return [
            'user_id' => $user->id,
            'month' => $month,
            'year' => $year,
            'base_salary' => $baseSalary,
            'total_work_days' => $totalWorkDays,
            'days_present' => $daysPresent,
            'total_late_days' => $daysLate,
            'total_absent_days' => $daysAbsent,
            'late_deduction' => 0,
            'absent_deduction' => 0,
            'deductions' => 0,
            'final_salary' => $finalSalary,
        ];
    }
    
    /**
     * Dapatkan jumlah hari kerja dalam sebulan (tidak termasuk akhir pekan)
     */
    public function getWorkingDaysInMonth(int $year, int $month): int
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $workDays = 0;
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            // Check if it's a weekday (Monday = 1 to Friday = 5)
            if ($current->dayOfWeek >= 1 && $current->dayOfWeek <= 5) {
                $workDays++;
            }
            $current->addDay();
        }
        
        return $workDays;
    }
    
    /**
     * Buat atau perbarui data gaji
     */
    public function saveSalary(array $data, int $createdBy): Salary
    {
        // Cek apakah record sudah ada
        $existing = Salary::where('user_id', $data['user_id'])
            ->where('month', $data['month'])
            ->where('year', $data['year'])
            ->first();

        $updateData = [
            'base_salary' => $data['base_salary'],
            'final_salary' => $data['final_salary'],
            'created_by' => $createdBy,
        ];

        // Hanya set status 'draft' untuk record baru, pertahankan status existing
        if (!$existing) {
            $updateData['status'] = 'draft';
        }

        return Salary::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'month' => $data['month'],
                'year' => $data['year'],
            ],
            $updateData
        );
    }
}
