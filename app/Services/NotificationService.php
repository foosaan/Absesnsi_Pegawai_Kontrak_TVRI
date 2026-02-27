<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a notification for a specific user
     */
    public static function send(int $userId, string $type, string $icon, string $color, string $message, ?string $detail = null, ?string $url = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'icon' => $icon,
            'color' => $color,
            'message' => $message,
            'detail' => $detail,
            'url' => $url,
        ]);
    }

    /**
     * Notify user: leave approved/rejected
     */
    public static function leaveProcessed(\App\Models\Leave $leave): void
    {
        $approved = $leave->status === 'approved';
        self::send(
            $leave->user_id,
            $approved ? 'leave_approved' : 'leave_rejected',
            $approved ? 'fa-check-circle' : 'fa-times-circle',
            $approved ? 'text-emerald-500' : 'text-red-500',
            'Cuti ' . $leave->type_label . ' ' . ($approved ? 'disetujui' : 'ditolak'),
            $leave->start_date->format('d M') . ' - ' . $leave->end_date->format('d M Y'),
            route('user.leaves')
        );
    }

    /**
     * Notify user: business trip approved/rejected
     */
    public static function businessTripProcessed(\App\Models\BusinessTrip $trip): void
    {
        $approved = $trip->status === 'approved';
        self::send(
            $trip->user_id,
            $approved ? 'trip_approved' : 'trip_rejected',
            $approved ? 'fa-check-circle' : 'fa-times-circle',
            $approved ? 'text-blue-500' : 'text-red-500',
            'Dinas luar ke ' . $trip->destination . ' ' . ($approved ? 'disetujui' : 'ditolak'),
            $trip->start_date->format('d M') . ' - ' . $trip->end_date->format('d M Y'),
            route('user.business-trips')
        );
    }

    /**
     * Notify user: salary slip available
     */
    public static function salaryCreated(\App\Models\Salary $salary): void
    {
        self::send(
            $salary->user_id,
            'salary',
            'fa-money-bill-wave',
            'text-emerald-500',
            'Slip gaji ' . $salary->period . ' tersedia',
            'Gaji pokok: Rp ' . number_format($salary->base_salary, 0, ',', '.'),
            route('user.salary')
        );
    }

    /**
     * Notify all users: new announcement
     */
    public static function announcementCreated(\App\Models\Announcement $announcement): void
    {
        $users = User::where('role', 'user')->pluck('id');
        foreach ($users as $userId) {
            self::send(
                $userId,
                'announcement',
                'fa-bullhorn',
                'text-amber-500',
                $announcement->title,
                \Illuminate\Support\Str::limit($announcement->content, 50),
                route('dashboard')
            );
        }
    }

    /**
     * Notify PSDM staff: new leave request
     */
    public static function newLeaveRequest(\App\Models\Leave $leave): void
    {
        $staffPsdm = User::where('role', 'staff_psdm')->pluck('id');
        foreach ($staffPsdm as $staffId) {
            self::send(
                $staffId,
                'pending_leave',
                'fa-calendar-minus',
                'text-amber-500',
                'Pengajuan cuti baru dari ' . $leave->user->name,
                $leave->type_label . ' (' . $leave->start_date->format('d M') . ' - ' . $leave->end_date->format('d M Y') . ')',
                route('staff.psdm.leaves', ['status' => 'pending'])
            );
        }
    }

    /**
     * Notify PSDM staff: new business trip request
     */
    public static function newBusinessTripRequest(\App\Models\BusinessTrip $trip): void
    {
        $staffPsdm = User::where('role', 'staff_psdm')->pluck('id');
        foreach ($staffPsdm as $staffId) {
            self::send(
                $staffId,
                'pending_trip',
                'fa-briefcase',
                'text-blue-500',
                'Pengajuan dinas luar dari ' . $trip->user->name,
                $trip->destination . ' (' . $trip->start_date->format('d M') . ' - ' . $trip->end_date->format('d M Y') . ')',
                route('staff.psdm.business-trips', ['status' => 'pending'])
            );
        }
    }
}
