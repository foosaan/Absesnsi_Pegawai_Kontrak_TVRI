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
        
        // Get attendance records for the month
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('check_in_time', [$startDate, $endDate])
            ->get();
        
        // Count statistics
        $totalWorkDays = $this->getWorkingDaysInMonth($year, $month);
        $daysPresent = $attendances->count();
        $daysLate = $attendances->where('status', 'late')->count();
        $daysAbsent = $totalWorkDays - $daysPresent;
        
        // Calculate deductions
        // Late deduction: 2% per late day
        // Absent deduction: 4% per absent day
        $lateDeduction = ($daysLate * 0.02) * $baseSalary;
        $absentDeduction = ($daysAbsent * 0.04) * $baseSalary;
        $totalDeductions = $lateDeduction + $absentDeduction;
        
        $finalSalary = $baseSalary - $totalDeductions;
        
        return [
            'user_id' => $user->id,
            'month' => $month,
            'year' => $year,
            'base_salary' => $baseSalary,
            'total_work_days' => $totalWorkDays,
            'days_present' => $daysPresent,
            'total_late_days' => $daysLate,
            'total_absent_days' => $daysAbsent,
            'late_deduction' => $lateDeduction,
            'absent_deduction' => $absentDeduction,
            'deductions' => $totalDeductions,
            'final_salary' => max(0, $finalSalary),
        ];
    }
    
    /**
     * Get number of working days in a month (exclude weekends)
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
     * Create or update salary record
     */
    public function saveSalary(array $data, int $createdBy): Salary
    {
        return Salary::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'month' => $data['month'],
                'year' => $data['year'],
            ],
            [
                'base_salary' => $data['base_salary'],
                'deductions' => $data['deductions'],
                'total_work_days' => $data['total_work_days'],
                'total_late_days' => $data['total_late_days'],
                'total_absent_days' => $data['total_absent_days'],
                'final_salary' => $data['final_salary'],
                'created_by' => $createdBy,
                'status' => 'draft',
            ]
        );
    }
}
