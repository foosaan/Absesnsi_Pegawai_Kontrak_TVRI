<?php

namespace App\Mail;

use App\Models\Leave;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public Leave $leave;
    public string $statusText;
    public string $statusColor;

    public function __construct(Leave $leave)
    {
        $this->leave = $leave;

        $this->statusText = match ($leave->status) {
            'approved' => 'Disetujui ✅',
            'rejected' => 'Ditolak ❌',
            default => ucfirst($leave->status),
        };

        $this->statusColor = match ($leave->status) {
            'approved' => '#10B981',
            'rejected' => '#EF4444',
            default => '#6B7280',
        };
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pengajuan Cuti {$this->statusText} - TVRI Presensi",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leave-status',
        );
    }
}
